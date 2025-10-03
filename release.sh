#!/bin/bash
set -e

# Usage: ./release.sh [major|minor|patch]
# Example: ./release.sh patch

if [ -z "$1" ]; then
  echo "Usage: $0 [major|minor|patch]"
  exit 1
fi

BUMP=$1
PROJECT=$(basename "$PWD")

# --- Get current version ---
if [ -f "style.css" ]; then
  CURVER=$(grep -i "Version:" style.css | head -n1 | sed -E 's/.*Version:[[:space:]]*//')
elif [ -f "${PROJECT}.php" ]; then
  CURVER=$(grep -i "Version:" "${PROJECT}.php" | head -n1 | sed -E 's/.*Version:[[:space:]]*//')
else
  echo "❌ No version source found (style.css or ${PROJECT}.php)"
  exit 1
fi

IFS='.' read -r MAJOR MINOR PATCH <<< "$CURVER"

case $BUMP in
  major)
    MAJOR=$((MAJOR+1)); MINOR=0; PATCH=0 ;;
  minor)
    MINOR=$((MINOR+1)); PATCH=0 ;;
  patch)
    PATCH=$((PATCH+1)) ;;
  *)
    echo "❌ Invalid bump type. Use: major, minor, patch"
    exit 1 ;;
esac

NEWVER="${MAJOR}.${MINOR}.${PATCH}"
ZIPNAME="${PROJECT}-v${NEWVER}.zip"

echo "🚀 Bumping $PROJECT $CURVER → $NEWVER"

# --- Update version (SSHFS-safe) ---
if [ -f "style.css" ]; then
  sed -E 's/^Version:.*/Version: '"${NEWVER}"'/' style.css > style.css.tmp && mv style.css.tmp style.css
  echo "✔ Updated style.css"
fi

if [ -f "${PROJECT}.php" ]; then
  sed -E 's/(\* Version:).*/\1 '"${NEWVER}"'/' "${PROJECT}.php" > tmpfile && mv tmpfile "${PROJECT}.php"
  echo "✔ Updated ${PROJECT}.php"
fi

# --- Update CHANGELOG.md ---
if [ -f CHANGELOG.md ]; then
  echo "## v${NEWVER} - $(date +%Y-%m-%d)" > .changelog.tmp
  git log $(git describe --tags --abbrev=0)..HEAD --pretty=format:"- %s" >> .changelog.tmp || true
  echo -e "\n\n$(cat CHANGELOG.md)" >> .changelog.tmp
  mv .changelog.tmp CHANGELOG.md
else
  echo "# Changelog" > CHANGELOG.md
  echo "## v${NEWVER} - $(date +%Y-%m-%d)" >> CHANGELOG.md
  git log --pretty=format:"- %s" >> CHANGELOG.md
fi
echo "✔ Updated CHANGELOG.md"

# --- Commit everything (safe, includes new/deleted files) ---
git add -A
git commit -m "chore(release): v${NEWVER}" || echo "ℹ Nothing to commit"
git push

# --- Tag + push tag ---
git tag -a "v${NEWVER}" -m "${PROJECT} v${NEWVER}"
git push origin "v${NEWVER}"

# --- Build Clean ZIP ---
cd ..
rsync -a "$PROJECT" "${PROJECT}-v${NEWVER}" \
  --exclude ".git" --exclude ".github" --exclude "node_modules" \
  --exclude "vendor" --exclude "*.map" --exclude "*.DS_Store"

zip -r "$ZIPNAME" "${PROJECT}-v${NEWVER}"
rm -rf "${PROJECT}-v${NEWVER}"
cd "$PROJECT"

# --- GitHub Release ---
gh release create "v${NEWVER}" "../$ZIPNAME" \
  --title "${PROJECT} v${NEWVER}" \
  --notes-file CHANGELOG.md

echo "🎉 Release $PROJECT v$NEWVER complete"
