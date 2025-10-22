#!/usr/bin/env bash
# Starscream smart releaser (force-local). One command:
#   ./release.sh major|minor|patch
set -euo pipefail

[[ "${1:-}" =~ ^(major|minor|patch)$ ]] || { echo "Usage: $0 major|minor|patch"; exit 1; }
BUMP="$1"

log(){ printf "🔹 %s\n" "$*"; }
ok(){ printf "✅ %s\n" "$*"; }
warn(){ printf "⚠ %s\n" "$*"; }
die(){ printf "❌ %s\n" "$*"; exit 1; }

# Always pass ONE string to avoid quoting bugs
run(){ bash -lc "$*"; }

PROJECT="$(basename "$PWD")"
STYLE_FILE="style.css"
MAIN_PHP="${PROJECT}.php" # fallback if you drop this into a plugin repo
IS_THEME=0; [[ -f "$STYLE_FILE" ]] && IS_THEME=1

# --- Git bootstrap (force-local flow) ---
if ! git rev-parse --is-inside-work-tree >/dev/null 2>&1; then
  log "Not a Git repo — initializing..."
  run "git init -b main"
fi
git config user.name  >/dev/null 2>&1 || run "git config user.name 'Eric Kowalewski'"
git config user.email >/dev/null 2>&1 || run "git config user.email 'emkowale@gmail.com'"

if git remote get-url origin >/dev/null 2>&1; then
  ORIGIN="$(git remote get-url origin)"
else
  ORIGIN="git@github.com:emkowale/${PROJECT}.git"
  log "Setting origin -> ${ORIGIN}"
  run "git remote add origin '${ORIGIN}'" || true
fi

# snapshot before release
run "git add -A"
run "git commit -m 'chore: snapshot before release' || true"

# --- Version helpers ---
read_file_version(){
  if [[ "$IS_THEME" == "1" && -f "$STYLE_FILE" ]]; then
    grep -Ei '^[[:space:]]*\**[[:space:]]*Version:' "$STYLE_FILE" | head -n1 | sed -E 's/.*Version:[[:space:]]*//'
  elif [[ -f "$MAIN_PHP" ]]; then
    grep -Ei '^[[:space:]]*\**[[:space:]]*Version:' "$MAIN_PHP" | head -n1 | sed -E 's/.*Version:[[:space:]]*//'
  else
    echo "0.0.0"
  fi
}
ver_max(){ printf "%s\n%s\n" "$1" "$2" | sort -V | tail -n1; }
tag_exists(){ git rev-parse -q --verify "refs/tags/$1" >/dev/null 2>&1; }

LATEST_TAG="$(git describe --tags --abbrev=0 2>/dev/null || echo 'v0.0.0')"
TAGVER="${LATEST_TAG#v}"
FILEVER="$(read_file_version)"
BASEVER="$(ver_max "$FILEVER" "$TAGVER")"

IFS='.' read -r MAJOR MINOR PATCH <<<"$BASEVER"
case "$BUMP" in
  major) ((MAJOR++)); MINOR=0; PATCH=0 ;;
  minor) ((MINOR++)); PATCH=0 ;;
  patch) ((PATCH++)) ;;
esac
NEWVER="${MAJOR}.${MINOR}.${PATCH}"
while tag_exists "v${NEWVER}"; do ((PATCH++)); NEWVER="${MAJOR}.${MINOR}.${PATCH}"; done
ok "Releasing ${PROJECT} ${BASEVER} → ${NEWVER}"

# --- Bump version (theme-first) ---
if [[ "$IS_THEME" == "1" && -f "$STYLE_FILE" ]]; then
  run "sed -E 's/^([[:space:]]*\\**[[:space:]]*Version:).*/\\1 ${NEWVER}/' '$STYLE_FILE' > .style.tmp && mv .style.tmp '$STYLE_FILE'"
  if ! grep -qi '^Update URI:' "$STYLE_FILE"; then
    run "awk 'NR==1{print;print \" * Update URI: https://github.com/emkowale/${PROJECT}\";next}1' '$STYLE_FILE' > .style2.tmp && mv .style2.tmp '$STYLE_FILE'"
  fi
  ok "Updated style.css"
elif [[ -f "$MAIN_PHP" ]]; then
  run "sed -E 's/^([[:space:]]*\\**[[:space:]]*Version:).*/\\1 ${NEWVER}/' '$MAIN_PHP' > .main.tmp && mv .main.tmp '$MAIN_PHP'"
  ok "Updated ${MAIN_PHP}"
else
  die "Neither style.css (theme) nor ${MAIN_PHP} (plugin) found."
fi

# --- CHANGELOG (works even without much history) ---
RANGE=""
if git rev-parse -q --verify "$LATEST_TAG" >/dev/null 2>&1 && [[ "$LATEST_TAG" != "v0.0.0" ]]; then
  RANGE="${LATEST_TAG}..HEAD"
fi
COMMITS="$(git log ${RANGE:-} --no-merges --pretty=format:'- %s' 2>/dev/null || true)"
[[ -z "$COMMITS" ]] && COMMITS="_No commit details available on this machine_"
run "printf '%s\n\n%s\n\n' \"## v${NEWVER} - $(date +%Y-%m-%d)\" \"$COMMITS\" > .changelog.tmp"
[[ -f CHANGELOG.md ]] && run "cat CHANGELOG.md >> .changelog.tmp"
run "mv .changelog.tmp CHANGELOG.md"
ok "Updated CHANGELOG.md"

# --- Commit bump, force-push main (local is truth) ---
run "git add -A"
run "git commit -m 'chore(release): v${NEWVER}' || true"
run "git branch -M main"
# ensure remote main exists or is updated; always force so local wins
run "git push -f origin main || true"

# --- Tag then push tag ---
run "git tag -a 'v${NEWVER}' -m '${PROJECT} v${NEWVER}' || true"
run "git push origin 'v${NEWVER}' || true"
ok "Tagged v${NEWVER}"

# --- Build clean ZIP ---
BUILD="../${PROJECT}-build"
run "rm -rf '$BUILD'"
run "mkdir -p '$BUILD/$PROJECT'"
EXCLUDES=( ".git" ".github" "node_modules" "vendor" "*.map" "*.DS_Store" ".idea" ".vscode" "release.sh" )
RSYNC_EXCLUDES=$(printf " --exclude %q" "${EXCLUDES[@]}")
run "rsync -a ./ '$BUILD/$PROJECT/' ${RSYNC_EXCLUDES}"
run "cd '$BUILD' && zip -rq '../${PROJECT}-v${NEWVER}.zip' '$PROJECT' && cd - >/dev/null"
run "rm -rf '$BUILD'"
ok "Built ZIP: ${PROJECT}-v${NEWVER}.zip"

# Validate theme version inside ZIP
if [[ "$IS_THEME" == "1" ]]; then
  if unzip -p "../${PROJECT}-v${NEWVER}.zip" "$PROJECT/style.css" | grep -Ei '^[[:space:]]*\**[[:space:]]*Version:[[:space:]]*'"$NEWVER" >/dev/null; then
    ok "ZIP validated (style.css Version: ${NEWVER})"
  else
    die "ZIP missing correct style.css Version ${NEWVER}"
  fi
fi

# --- Publish GitHub Release (if logged in) ---
if command -v gh >/dev/null 2>&1 && gh auth status >/dev/null 2>&1; then
  run "gh release create 'v${NEWVER}' '../${PROJECT}-v${NEWVER}.zip' --title '${PROJECT} v${NEWVER}' --notes 'Automated release for ${PROJECT} ${NEWVER}' || true"
  ok "GitHub release published v${NEWVER}"
else
  warn "GitHub CLI not authenticated or not installed. To publish, run: gh auth login"
fi

ok "Release ${PROJECT} v${NEWVER} complete."
