#!/usr/bin/env bash
# Starscream theme release script — bump style.css version, changelog, commit/tag/push, zip, GitHub release.
set -euo pipefail

# ==== CONFIG ==================================================================
OWNER="emkowale"
REPO="starscream"
THEME_SLUG="starscream"
MAIN_FILE="style.css"
REMOTE_URL="git@github.com:${OWNER}/${REPO}.git"

# ==== UI HELPERS ==============================================================
C_RESET=$'\033[0m'; C_CYAN=$'\033[1;36m'; C_YEL=$'\033[1;33m'; C_RED=$'\033[1;31m'; C_GRN=$'\033[1;32m'
step(){ printf "${C_CYAN}🔷 %s${C_RESET}\n" "$*"; }
ok(){   printf "${C_GRN}✅ %s${C_RESET}\n" "$*"; }
warn(){ printf "${C_YEL}⚠️  %s${C_RESET}\n" "$*"; }
die(){  printf "${C_RED}❌ %s${C_RESET}\n" "$*"; exit 1; }
trap 'printf "${C_RED}❌ Release failed at line %s${C_RESET}\n" "$LINENO"' ERR

is_interactive(){ [[ -t 0 && -t 1 ]]; }
gh_env_token_present(){ [[ -n "${GH_TOKEN:-}" || -n "${GITHUB_TOKEN:-}" ]]; }
ensure_gh_auth(){
  command -v gh >/dev/null 2>&1 || return 1

  if gh_env_token_present; then
    ok "Using GitHub token from environment for release publish"
    return 0
  fi

  if gh auth status -h github.com >/dev/null 2>&1; then
    return 0
  fi

  if ! is_interactive; then
    return 1
  fi

  warn "GitHub release auth is missing or invalid."
  warn "Starting interactive gh auth login for github.com"

  if gh auth login -h github.com -p ssh -w; then
    gh auth status -h github.com >/dev/null 2>&1
    return $?
  fi

  warn "Browser login failed or was cancelled; trying terminal login"
  gh auth login -h github.com -p ssh && gh auth status -h github.com >/dev/null 2>&1
}

# ==== ARGS / TOOL CHECKS ======================================================
BUMP_TYPE="${1:-patch}"
[[ "$BUMP_TYPE" =~ ^(major|minor|patch)$ ]] || die "Usage: ./release.sh {major|minor|patch}"

command -v php >/dev/null || die "php not found"
command -v zip >/dev/null || die "zip not found"
command -v rsync >/dev/null || die "rsync not found"
SKIP_GITHUB_RELEASE="${STARSCREAM_SKIP_GITHUB_RELEASE:-0}"
GIT_OK=1
if ! command -v git >/dev/null; then
  GIT_OK=0
  warn "git not found; will skip git operations"
fi

# ==== LOCATE REPO ROOT & MAIN FILE ===========================================
ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
cd "$ROOT"
GIT_BOOTSTRAPPED=0
if [[ "$GIT_OK" -eq 1 ]] && [[ ! -d ".git" ]]; then
  step "No .git directory found; initializing local repository"
  git init -b main >/dev/null 2>&1 || git init >/dev/null
  GIT_BOOTSTRAPPED=1
  ok "Initialized git repository in ${ROOT}"
fi

if [[ ! -f "${MAIN_FILE}" ]]; then
  die "Cannot find ${MAIN_FILE} in ${ROOT}"
fi

# ==== GIT: SELF-HEAL & SYNC ===================================================
if [[ "$GIT_OK" -eq 1 ]]; then
  step "Preparing git state"
  if git remote get-url origin >/dev/null 2>&1; then
    git remote set-url origin "$REMOTE_URL" >/dev/null 2>&1 || true
  else
    git remote add origin "$REMOTE_URL" >/dev/null 2>&1 || true
  fi
  git rebase --abort >/dev/null 2>&1 || true
  git merge --abort  >/dev/null 2>&1 || true
  git reset --merge  >/dev/null 2>&1 || true

  if ! git rev-parse --abbrev-ref HEAD 2>/dev/null | grep -q '^main$'; then
    if git show-ref --verify --quiet refs/heads/main; then
      git switch main >/dev/null 2>&1 || git checkout main >/dev/null 2>&1 || true
    else
      git switch -c main >/dev/null 2>&1 || git checkout -b main >/dev/null 2>&1 || true
    fi
  fi

  step "Fetching remote branch & tags"
  git fetch origin main --tags || true
  if git show-ref --verify --quiet refs/remotes/origin/main; then
    git rev-parse --abbrev-ref --symbolic-full-name @{u} >/dev/null 2>&1 || git branch --set-upstream-to=origin/main main >/dev/null 2>&1 || true
    git merge --allow-unrelated-histories -s ours --no-edit origin/main -m "stitch: adopt origin/main (prefer local files)" >/dev/null 2>&1 || true
  fi
  [[ "$GIT_BOOTSTRAPPED" -eq 1 ]] && ok "Git repository bootstrapped"
  ok "Git ready"
else
  warn "Skipping git prep/fetch (no repo)."
fi

# ==== GITHUB RELEASE PREFLIGHT ================================================
if [[ "$SKIP_GITHUB_RELEASE" == "1" ]]; then
  warn "GitHub release publishing disabled via STARSCREAM_SKIP_GITHUB_RELEASE=1"
elif [[ "$GIT_OK" -eq 0 ]]; then
  warn "Skipping GitHub release preflight (git unavailable)"
else
  command -v gh >/dev/null 2>&1 || die "gh not found. Install GitHub CLI or run with STARSCREAM_SKIP_GITHUB_RELEASE=1"
  step "Checking GitHub release auth"
  ensure_gh_auth || die "GitHub release auth failed. Run 'gh auth login -h github.com' or export GH_TOKEN, then rerun."
  ok "GitHub release auth ready"
fi

# ==== VERSION DISCOVERY =======================================================
step "Reading current version from ${MAIN_FILE}"
read_version_php=$(cat <<'PHP'
$path = $argv[1];
$src = file_get_contents($path);
if ($src === false) { fwrite(STDERR, "read fail\n"); exit(1); }

$versions = [];
if (preg_match_all('/(?mi)^\s*(?:\*\s*)?Version\s*:\s*([0-9]+\.[0-9]+\.[0-9]+)/', $src, $m)) {
  foreach ($m[1] as $v) $versions[] = $v;
}
if (empty($versions)) { echo "0.0.0"; exit; }
usort($versions, 'version_compare');
echo end($versions);
PHP
)
BASE_VER="$(php -r "$read_version_php" "$MAIN_FILE")"
[[ -n "$BASE_VER" ]] || BASE_VER="0.0.0"

if [[ "$GIT_OK" -eq 1 ]]; then
  latest_tag="$(git tag | grep -E '^v[0-9]+\.[0-9]+\.[0-9]+$' | sed 's/^v//' | sort -V | tail -n1 || true)"
  ver_ge(){ printf '%s\n%s\n' "$1" "$2" | sort -V -r | head -n1 | grep -qx "$1"; }
  if [[ -n "$latest_tag" ]] && ver_ge "$latest_tag" "$BASE_VER"; then
    BASE_VER="$latest_tag"
  fi
fi
ok "Base version: $BASE_VER"

IFS='.' read -r MAJ MIN PAT <<<"$BASE_VER"
case "$BUMP_TYPE" in
  major) ((MAJ+=1)); MIN=0; PAT=0 ;;
  minor) ((MIN+=1)); PAT=0 ;;
  patch) ((PAT+=1)) ;;
esac
NEXT="${MAJ}.${MIN}.${PAT}"

if [[ "$GIT_OK" -eq 1 ]]; then
  tag_exists(){ git rev-parse -q --verify "refs/tags/v$1" >/dev/null 2>&1; }
  while tag_exists "$NEXT"; do
    ((PAT+=1)); NEXT="${MAJ}.${MIN}.${PAT}"
  done
fi
printf "${C_CYAN}🚀 Preparing release v%s${C_RESET}\n" "$NEXT"

# ==== SAFE CSS HEADER UPDATER ================================================
step "Updating ${MAIN_FILE} safely"
update_style_php=$(cat <<'PHP'
$path = $argv[1];
$ver  = $argv[2];

$src = file_get_contents($path);
if ($src === false) { fwrite(STDERR, "read fail\n"); exit(1); }

$src = preg_replace("/\r\n?/", "\n", $src);
$lines = preg_split("/\n/", $src);
$limit = min(200, count($lines));
$start = -1; $end = -1;

for ($i = 0; $i < $limit; $i++) {
  if (preg_match('/^\s*\/\*/', $lines[$i])) { $start = $i; break; }
}
if ($start >= 0) {
  for ($j = $start; $j < min($start + 120, count($lines)); $j++) {
    if (preg_match('/\*\//', $lines[$j])) { $end = $j; break; }
  }
}

if ($start < 0 || $end < 0) {
  array_splice($lines, 0, 0, ["/*", " * Theme Name: Starscream", " * Version: " . $ver, " */"]);
} else {
  $found = false;
  for ($k = $start; $k <= $end; $k++) {
    if (preg_match('/^\s*(?:\*\s*)?Version\s*:/i', $lines[$k])) {
      $lines[$k] = preg_replace('/^(\s*(?:\*\s*)?Version\s*:)\s*.*/i', '$1 ' . $ver, $lines[$k]);
      $found = true;
      break;
    }
  }
  if (!$found) {
    $insert_at = $start + 1;
    for ($k = $start; $k <= $end; $k++) {
      if (preg_match('/^\s*\*\s*Theme\s+Name\s*:/i', $lines[$k])) {
        $insert_at = $k + 1;
        break;
      }
    }
    array_splice($lines, $insert_at, 0, " * Version: " . $ver);
  }
}

$out = implode("\n", $lines);
if (!preg_match('/(?mi)^\s*(?:\*\s*)?Version\s*:\s*' . preg_quote($ver, '/') . '\b/', $out)) {
  fwrite(STDERR, "warn: style.css header missing Version after update\n");
}

if (file_put_contents($path, $out) === false) { fwrite(STDERR, "write fail\n"); exit(1); }
PHP
)
php -r "$update_style_php" "$MAIN_FILE" "$NEXT"
ok "Updated ${MAIN_FILE} to v${NEXT}"

# ==== CHANGELOG AUTO-UPDATE ===================================================
step "Updating CHANGELOG.md"
CHANGELOG="CHANGELOG.md"
TODAY="$(date +%Y-%m-%d)"

if [[ ! -f "$CHANGELOG" ]]; then
  printf "# Changelog\n\n## [%s] - %s\n\n" "$NEXT" "$TODAY" > "$CHANGELOG"
  ok "Created new CHANGELOG.md"
else
  if grep -qE '^## \[Unreleased\]' "$CHANGELOG"; then
    tmp=$(mktemp)
    awk -v ver="$NEXT" -v today="$TODAY" '
      /^## \[Unreleased\]/ { print; print ""; print "## [" ver "] - " today; next }
      { print }
    ' "$CHANGELOG" > "$tmp" && mv "$tmp" "$CHANGELOG"
  else
    tmp=$(mktemp)
    awk -v ver="$NEXT" -v today="$TODAY" '
      NR==1 { print; print ""; print "## [" ver "] - " today; next }
      { print }
    ' "$CHANGELOG" > "$tmp" && mv "$tmp" "$CHANGELOG"
  fi
  ok "Prepared changelog section [${NEXT}]"
fi

LOG_FILE="$(mktemp)"
if [[ "$GIT_OK" -eq 1 ]]; then
  HAS_HEAD=0
  if git rev-parse --verify HEAD >/dev/null 2>&1; then
    HAS_HEAD=1
  fi

  PREV_TAG=""
  RANGE=""
  if [[ "$HAS_HEAD" -eq 1 ]]; then
    PREV_TAG="$(git tag -l 'v[0-9]*' | sort -V | tail -n2 | head -n1)"
    if [[ -n "$PREV_TAG" ]]; then
      RANGE="${PREV_TAG}..HEAD"
    fi
  fi

  if [[ "$HAS_HEAD" -eq 0 ]]; then
    echo "* Release generated from local working tree" > "$LOG_FILE"
  elif [[ -n "$RANGE" ]]; then
    git log --no-merges --pretty=format:'* %s (%h)' "$RANGE" > "$LOG_FILE" 2>/dev/null || true
  else
    git log --no-merges --pretty=format:'* %s (%h)' --max-count=100 > "$LOG_FILE" 2>/dev/null || true
  fi
else
  echo "* Manual release (git unavailable)" > "$LOG_FILE"
fi

if [[ ! -s "$LOG_FILE" ]]; then
  echo "* Internal updates" > "$LOG_FILE"
fi

tmp=$(mktemp)
awk -v ver="$NEXT" -v lf="$LOG_FILE" '
  {
    print
    if (!done && $0 ~ "^## \\[" ver "\\]") {
      print ""
      print "### Changes"
      while ((getline line < lf) > 0) print line
      close(lf)
      print ""
      done=1
    }
  }
' "$CHANGELOG" > "$tmp" && mv "$tmp" "$CHANGELOG"
rm -f "$LOG_FILE"

if [[ "$GIT_OK" -eq 1 ]]; then
  git add "$CHANGELOG"
fi

# ==== COMMIT / TAG / PUSH =====================================================
if [[ "$GIT_OK" -eq 1 ]]; then
  step "Committing & tagging"
  if git rev-parse --verify HEAD >/dev/null 2>&1; then
    git add "$MAIN_FILE" "$CHANGELOG"
  else
    git add -A
  fi
  git commit -m "chore(release): v${NEXT}" >/dev/null 2>&1 || warn "Nothing to commit (files already updated)"
  git rev-parse --verify HEAD >/dev/null 2>&1 || die "Unable to create a git commit; cannot tag/push."
  git tag -f "v${NEXT}"

  step "Pushing branch & tag"
  if ! git push origin main; then
    warn "Push rejected; refetching & stitching then retry"
    git fetch origin main --tags || true
    git merge --allow-unrelated-histories -s ours --no-edit origin/main -m "sync: prefer local files" || true
    git push origin main || warn "Could not push main (continuing)"
  fi
  git push -f origin "v${NEXT}" || warn "Could not push tag v${NEXT}"
  ok "Git pushed"
else
  warn "Skipping commit/tag/push (no repo)."
fi

# ==== BUILD ARTIFACT ==========================================================
step "Building zip artifact"
ART_DIR="artifacts"
PKG_DIR="package/${THEME_SLUG}"
ZIP_NAME="${THEME_SLUG}-v${NEXT}.zip"
rm -rf "$PKG_DIR" "$ART_DIR"
mkdir -p "$PKG_DIR" "$ART_DIR"
RSYNC_EXCLUDES=(
  --exclude ".git/"
  --exclude "artifacts/"
  --exclude "package/"
  --exclude ".github/"
  --exclude ".DS_Store"
  --exclude "release.sh"
)
rsync -a --delete "${RSYNC_EXCLUDES[@]}" ./ "$PKG_DIR/"
( cd "package" && zip -qr "../${ART_DIR}/${ZIP_NAME}" "${THEME_SLUG}" )
ok "Built ${ART_DIR}/${ZIP_NAME}"

# ==== GITHUB RELEASE ===========================================================
if [[ "$SKIP_GITHUB_RELEASE" == "1" ]]; then
  warn "Skipped GitHub release (STARSCREAM_SKIP_GITHUB_RELEASE=1)"
elif [[ "$GIT_OK" -eq 0 ]]; then
  warn "Skipped GitHub release (git unavailable)"
else
  step "Publishing GitHub release v${NEXT}"
  if gh release view "v${NEXT}" >/dev/null 2>&1; then
    warn "Release exists — updating asset"
    gh release upload "v${NEXT}" "${ART_DIR}/${ZIP_NAME}" --clobber >/dev/null || die "Could not upload release asset"
  else
    gh release create "v${NEXT}" "${ART_DIR}/${ZIP_NAME}" -t "v${NEXT}" -n "Release ${NEXT}" >/dev/null || die "Could not create GitHub release"
  fi
  ok "Release v${NEXT} published"
fi

rm -rf package

printf "${C_GRN}🎉 All done: ${ART_DIR}/${ZIP_NAME}${C_RESET}\n"
