#!/bin/bash
set -euo pipefail

# Starscream release automation (smart Git + CHANGELOG)
# Usage: ./release.sh [major|minor|patch]

BUMP="${1:-}"
if [[ -z "$BUMP" ]]; then
  echo "Usage: $0 [major|minor|patch]"
  exit 1
fi

PROJECT="$(basename "$PWD")"
ZIPNAME="../${PROJECT}-vNEWVER.zip"
REPO_SSH="git@github.com:emkowale/${PROJECT}.git"

# ---------- Helper: read version ----------
read_file_version() {
  if [[ -f "style.css" ]]; then
    grep -Ei '^[[:space:]]*\**[[:space:]]*Version:' style.css | head -n1 | sed -E 's/.*Version:[[:space:]]*//'
  else
    echo "0.0.0"
  fi
}

# ---------- Safe Git init if missing ----------
if ! git rev-parse --is-inside-work-tree >/dev/null 2>&1; then
  echo "⚠ Not a Git repo — initializing new repository..."
  git init -b main
  git remote add origin "$REPO_SSH" || true
  git add -A
  git commit -m "Initial import for Starscream theme"
  git branch -M main
  git push -u origin main || echo "⚠ Push failed (verify SSH key or repo exists)."
fi

# ---------- Version bump logic ----------
ver_max() { printf "%s\n%s\n" "$1" "$2" | sort -V | tail -n1; }
tag_exists() { git rev-parse -q --verify "refs/tags/$1" >/dev/null 2>&1; }

LATEST_TAG="$(git describe --tags --abbrev=0 2>/dev/null || echo "v0.0.0")"
TAGVER="${LATEST_TAG#v}"
FILEVER="$(read_file_version)"
BASEVER="$(ver_max "$FILEVER" "$TAGVER")"

IFS='.' read -r MAJOR MINOR PATCH <<<"$BASEVER"
case "$BUMP" in
  major) ((MAJOR++)); MINOR=0; PATCH=0 ;;
  minor) ((MINOR++)); PATCH=0 ;;
  patch) ((PATCH++)) ;;
  *) echo "❌ Invalid bump type. Use: major, minor, patch"; exit 1 ;;
esac
NEWVER="${MAJOR}.${MINOR}.${PATCH}"

# ---------- Update style.css ----------
sed -E "s/^([[:space:]]*\**[[:space:]]*Version:).*/\1 ${NEWVER}/" style.css > style.css.tmp && mv style.css.tmp style.css
echo "✔ Updated style.css → v${NEWVER}"

# ---------- Update CHANGELOG.md ----------
CHANGELOG="CHANGELOG.md"
{
  echo "## v${NEWVER} - $(date +%Y-%m-%d)"
  if git log -1 --oneline >/dev/null 2>&1; then
    echo
    git log -n 10 --pretty=format:"- %s" | grep -Ev '^(chore|bump|merge)' || echo "_No commits logged_"
  else
    echo
    echo "_Local-only release, no Git history available_"
  fi
  echo
  [[ -f "$CHANGELOG" ]] && cat "$CHANGELOG"
} > .changelog.tmp
mv .changelog.tmp "$CHANGELOG"
echo "✔ Updated CHANGELOG.md"

# ---------- Commit, Tag, Push ----------
git add -A
git commit -m "chore(release): v${NEWVER}" || echo "ℹ Nothing to commit"
git tag -a "v${NEWVER}" -m "${PROJECT} v${NEWVER}"
git push origin main || echo "⚠ Could not push main"
git push origin "v${NEWVER}" || echo "⚠ Could not push tag"

# ---------- Build ZIP ----------
WORKDIR="../${PROJECT}-build"
rm -rf "$WORKDIR"
mkdir -p "$WORKDIR/$PROJECT"
rsync -a ./ "$WORKDIR/$PROJECT/" --exclude ".git" --exclude ".vscode" --exclude "node_modules" --exclude "release.sh"

cd "$WORKDIR"
zip -rq "../${PROJECT}-v${NEWVER}.zip" "$PROJECT"
cd ..
rm -rf "$WORKDIR"
cd "$PROJECT"

echo "✔ Built ZIP: ${PROJECT}-v${NEWVER}.zip"

# ---------- GitHub release ----------
if command -v gh >/dev/null 2>&1; then
  gh release create "v${NEWVER}" "../${PROJECT}-v${NEWVER}.zip" --title "${PROJECT} v${NEWVER}" --notes "Auto-release for Starscream ${NEWVER}"
  echo "✔ Published GitHub release v${NEWVER}"
else
  echo "⚠ GitHub CLI not found; run manually:"
  echo "   gh release create v${NEWVER} ../${PROJECT}-v${NEWVER}.zip --title \"${PROJECT} v${NEWVER}\""
fi

echo "🎉 Starscream v${NEWVER} release complete!"
