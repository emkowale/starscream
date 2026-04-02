#!/usr/bin/env bash
# God-mode release script for a WordPress codebase.
# The CURRENT working tree is the source of truth.
# GitHub main is updated to match this tree on release.

set -euo pipefail

# ==== CONFIG ==================================================================
OWNER="emkowale"
REPO="starscream"

# Folder name inside generated zip
PACKAGE_SLUG="starscream"

# Main file that contains the WP header with Version:
# Theme example: style.css
# Plugin example: starscream.php
MAIN_FILE="style.css"

REMOTE_URL="git@github.com:${OWNER}/${REPO}.git"
UPDATE_URI="https://github.com/${OWNER}/${REPO}"
DEFAULT_BRANCH="main"

# 1 = local site always wins
SOURCE_OF_TRUTH_MODE=1

EXCLUDES=(
  ".git/"
  "artifacts/"
  "package/"
  ".github/"
  ".DS_Store"
)

# ==== UI ======================================================================
C_RESET=$'\033[0m'
C_CYAN=$'\033[1;36m'
C_YEL=$'\033[1;33m'
C_RED=$'\033[1;31m'
C_GRN=$'\033[1;32m'

step(){ printf "${C_CYAN}%s${C_RESET}\n" "$*"; }
ok(){   printf "${C_GRN}%s${C_RESET}\n" "$*"; }
warn(){ printf "${C_YEL}%s${C_RESET}\n" "$*"; }
die(){  printf "${C_RED}%s${C_RESET}\n" "$*"; exit 1; }

trap 'printf "${C_RED}Release failed at line %s${C_RESET}\n" "$LINENO"' ERR

# ==== ARGS / TOOLS ============================================================
BUMP_TYPE="${1:-patch}"
[[ "$BUMP_TYPE" =~ ^(major|minor|patch)$ ]] || die "Usage: ./release.sh {major|minor|patch}"

command -v php   >/dev/null || die "php not found"
command -v zip   >/dev/null || die "zip not found"
command -v rsync >/dev/null || die "rsync not found"

GIT_OK=1
if ! command -v git >/dev/null; then
  GIT_OK=0
  warn "git not found; git/tag/release steps will be skipped"
fi

ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
SNAPSHOT_DIR=""
GIT_BOOTSTRAPPED=0

# ==== HELPERS =================================================================
make_rsync_excludes() {
  local args=()
  local item
  for item in "${EXCLUDES[@]}"; do
    args+=(--exclude "$item")
  done
  printf '%s\0' "${args[@]}"
}

snapshot_worktree() {
  SNAPSHOT_DIR="$(mktemp -d)"
  local -a rsync_excludes=()
  mapfile -d '' -t rsync_excludes < <(make_rsync_excludes)
  rsync -a "${rsync_excludes[@]}" ./ "${SNAPSHOT_DIR}/"
}

restore_worktree() {
  [[ -n "$SNAPSHOT_DIR" && -d "$SNAPSHOT_DIR" ]] || return 0
  local -a rsync_excludes=()
  mapfile -d '' -t rsync_excludes < <(make_rsync_excludes)
  rsync -a --delete "${rsync_excludes[@]}" "${SNAPSHOT_DIR}/" ./
}

cleanup_snapshot() {
  [[ -n "$SNAPSHOT_DIR" && -d "$SNAPSHOT_DIR" ]] && rm -rf "$SNAPSHOT_DIR"
}
trap cleanup_snapshot EXIT

clear_worktree_for_checkout() {
  # After snapshotting, make the working tree safe for branch reset/checkout.
  # This is the key fix for your current failure.
  git reset --hard >/dev/null 2>&1 || true
  git clean -fd >/dev/null 2>&1 || true
}

ver_ge() {
  printf '%s\n%s\n' "$1" "$2" | sort -V -r | head -n1 | grep -qx "$1"
}

tag_exists_local() {
  git rev-parse -q --verify "refs/tags/v$1" >/dev/null 2>&1
}

tag_exists_remote() {
  git ls-remote --exit-code --tags origin "refs/tags/v$1" >/dev/null 2>&1
}

locate_source() {
  if [[ -f "${PACKAGE_SLUG}/${MAIN_FILE}" ]]; then
    SRC_DIR="${PACKAGE_SLUG}"
    MAIN_PATH="${PACKAGE_SLUG}/${MAIN_FILE}"
  elif [[ -f "${MAIN_FILE}" ]]; then
    SRC_DIR="."
    MAIN_PATH="${MAIN_FILE}"
  else
    die "Cannot find ${MAIN_FILE} at repo root or under ${PACKAGE_SLUG}/"
  fi
}

read_current_version() {
  local php_code
  php_code=$(cat <<'PHP'
$path = $argv[1];
$src = file_get_contents($path);
if ($src === false) { fwrite(STDERR, "read fail\n"); exit(1); }
if (preg_match('/(?mi)^\s*(?:\*\s*)?Version\s*:\s*([0-9]+\.[0-9]+\.[0-9]+)/', $src, $m)) {
  echo trim($m[1]);
} else {
  echo "0.0.0";
}
PHP
)
  php -r "$php_code" "$MAIN_PATH"
}

update_main_file_version() {
  local version="$1"
  local php_code
  php_code=$(cat <<'PHP'
$path = $argv[1];
$ver  = $argv[2];
$uri  = $argv[3];

$src = file_get_contents($path);
if ($src === false) { fwrite(STDERR, "read fail\n"); exit(1); }
$src = preg_replace("/\r\n?/", "\n", $src);

$lines = preg_split("/\n/", $src);
$limit = min(240, count($lines));
$start = -1;
$end = -1;

for ($i = 0; $i < $limit; $i++) {
  if (preg_match('/^\s*\/\*/', $lines[$i])) { $start = $i; break; }
}
if ($start >= 0) {
  for ($j = $start; $j < min($start + 140, count($lines)); $j++) {
    if (preg_match('/\*\//', $lines[$j])) { $end = $j; break; }
  }
}

if ($start < 0 || $end < 0) {
  $header = [
    '/*',
    ' * Version: ' . $ver,
    ' * Update URI: ' . $uri,
    ' */',
    ''
  ];
  array_splice($lines, 0, 0, $header);
} else {
  for ($k = $start; $k <= $end; $k++) {
    if (preg_match('/^\s*(?:\*\s*)?Version\s*:/i', $lines[$k])) $lines[$k] = null;
    if (preg_match('/^\s*(?:\*\s*)?Update\s+URI\s*:/i', $lines[$k])) $lines[$k] = null;
  }

  $tmp = [];
  foreach ($lines as $ln) if ($ln !== null) $tmp[] = $ln;
  $lines = $tmp;

  $end = -1;
  for ($j = $start; $j < min($start + 140, count($lines)); $j++) {
    if (preg_match('/\*\//', $lines[$j])) { $end = $j; break; }
  }

  if ($end < 0) {
    $lines[] = ' * Version: ' . $ver;
    $lines[] = ' * Update URI: ' . $uri;
    $lines[] = ' */';
  } else {
    array_splice($lines, $end, 0, [
      ' * Version: ' . $ver,
      ' * Update URI: ' . $uri
    ]);
  }
}

$out = implode("\n", $lines);

if (file_put_contents($path, $out) === false) {
  fwrite(STDERR, "write fail\n");
  exit(1);
}
PHP
)
  php -r "$php_code" "$MAIN_PATH" "$version" "$UPDATE_URI"
}

update_changelog() {
  local version="$1"
  local changelog="CHANGELOG.md"
  local today
  today="$(date +%Y-%m-%d)"

  step "Updating CHANGELOG.md"

  if [[ ! -f "$changelog" ]]; then
    printf "# Changelog\n\n## [%s] - %s\n\n" "$version" "$today" > "$changelog"
    ok "Created CHANGELOG.md"
  elif grep -qE "^## \[${version}\]" "$changelog"; then
    warn "CHANGELOG already has section [${version}]"
  elif grep -qE '^## \[Unreleased\]' "$changelog"; then
    local tmp
    tmp="$(mktemp)"
    awk -v ver="$version" -v today="$today" '
      /^## \[Unreleased\]/ { print; print ""; print "## ["ver"] - "today; next }
      { print }
    ' "$changelog" > "$tmp" && mv "$tmp" "$changelog"
    ok "Added [${version}] under [Unreleased]"
  else
    local tmp
    tmp="$(mktemp)"
    awk -v ver="$version" -v today="$today" '
      NR==1 { print; print ""; print "## ["ver"] - "today; next }
      { print }
    ' "$changelog" > "$tmp" && mv "$tmp" "$changelog"
    ok "Prepended [${version}] section"
  fi

  local log_file
  log_file="$(mktemp)"

  if [[ "$GIT_OK" -eq 1 ]] && git rev-parse --verify HEAD >/dev/null 2>&1; then
    local prev_tag
    prev_tag="$(git tag -l 'v[0-9]*' | sort -V | tail -n1 || true)"
    if [[ -n "$prev_tag" ]]; then
      git log --no-merges --pretty=format:'* %s (%h)' "${prev_tag}..HEAD" > "$log_file" 2>/dev/null || true
    else
      git log --no-merges --pretty=format:'* %s (%h)' --max-count=100 > "$log_file" 2>/dev/null || true
    fi
  else
    echo "* Release generated from local working tree" > "$log_file"
  fi

  [[ -s "$log_file" ]] || echo "* Internal updates" > "$log_file"

  if ! grep -qE "^## \[${version}\]" "$changelog"; then
    printf "\n## [%s] - %s\n\n" "$version" "$today" >> "$changelog"
  fi

  local tmp
  tmp="$(mktemp)"
  awk -v ver="$version" -v lf="$log_file" '
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
  ' "$changelog" > "$tmp" && mv "$tmp" "$changelog"

  rm -f "$log_file"
  ok "CHANGELOG updated"
}

build_zip() {
  local version="$1"
  local art_dir="artifacts"
  local pkg_root="package"
  local pkg_dir="${pkg_root}/${PACKAGE_SLUG}"
  local zip_name="${PACKAGE_SLUG}-v${version}.zip"

  step "Building zip artifact"

  rm -rf "$pkg_dir" "$art_dir"
  mkdir -p "$pkg_dir" "$art_dir"

  local -a rsync_excludes=()
  mapfile -d '' -t rsync_excludes < <(make_rsync_excludes)

  if [[ "$SRC_DIR" == "." ]]; then
    rsync -a --delete "${rsync_excludes[@]}" ./ "$pkg_dir/"
  else
    rsync -a --delete "${rsync_excludes[@]}" "${SRC_DIR}/" "$pkg_dir/"
  fi

  (
    cd "$pkg_root"
    zip -qr "../${art_dir}/${zip_name}" "${PACKAGE_SLUG}"
  )

  rm -rf "$pkg_root"
  ok "Built ${art_dir}/${zip_name}"
}

publish_github_release() {
  local version="$1"
  local zip_path="artifacts/${PACKAGE_SLUG}-v${version}.zip"

  if [[ "$GIT_OK" -eq 1 ]] && command -v gh >/dev/null 2>&1; then
    step "Publishing GitHub release v${version}"
    if gh auth status >/dev/null 2>&1; then
      if gh release view "v${version}" >/dev/null 2>&1; then
        warn "Release exists; updating asset"
        gh release upload "v${version}" "$zip_path" --clobber >/dev/null || warn "Could not upload asset"
      else
        gh release create "v${version}" "$zip_path" -t "v${version}" -n "Release ${version}" >/dev/null || warn "Could not create release"
      fi
      ok "Release v${version} published"
    else
      warn "gh is installed but not authenticated; skipping GitHub release"
    fi
  else
    warn "Skipping GitHub release (git/gh unavailable)"
  fi
}

cd "$ROOT"
locate_source

# ==== BOOTSTRAP ===============================================================
if [[ "$GIT_OK" -eq 1 ]] && [[ ! -d ".git" ]]; then
  step "No .git directory found; initializing local repository"
  git init -b "$DEFAULT_BRANCH" >/dev/null 2>&1 || git init >/dev/null
  GIT_BOOTSTRAPPED=1
  ok "Initialized git repository in ${ROOT}"
fi

# ==== GIT PREP ================================================================
if [[ "$GIT_OK" -eq 1 ]]; then
  step "Preparing git state"

  if git remote get-url origin >/dev/null 2>&1; then
    git remote set-url origin "$REMOTE_URL" >/dev/null 2>&1 || true
  else
    git remote add origin "$REMOTE_URL" >/dev/null 2>&1 || true
  fi

  git rebase --abort >/dev/null 2>&1 || true
  git merge --abort >/dev/null 2>&1 || true

  snapshot_worktree

  if ! git rev-parse --abbrev-ref HEAD 2>/dev/null | grep -q "^${DEFAULT_BRANCH}$"; then
    if git show-ref --verify --quiet "refs/heads/${DEFAULT_BRANCH}"; then
      git switch "$DEFAULT_BRANCH" >/dev/null 2>&1 || git checkout "$DEFAULT_BRANCH" >/dev/null 2>&1 || true
    else
      git switch -c "$DEFAULT_BRANCH" >/dev/null 2>&1 || git checkout -b "$DEFAULT_BRANCH" >/dev/null 2>&1 || true
    fi
  fi

  step "Fetching remote branch and tags"
  git fetch origin "$DEFAULT_BRANCH" --tags >/dev/null 2>&1 || git fetch origin --tags >/dev/null 2>&1 || true

  if git show-ref --verify --quiet "refs/remotes/origin/${DEFAULT_BRANCH}"; then
    git rev-parse --abbrev-ref --symbolic-full-name '@{u}' >/dev/null 2>&1 || \
      git branch --set-upstream-to="origin/${DEFAULT_BRANCH}" "$DEFAULT_BRANCH" >/dev/null 2>&1 || true

    LOCAL_HAS_HEAD=0
    if git rev-parse --verify HEAD >/dev/null 2>&1; then
      LOCAL_HAS_HEAD=1
    fi

    RELATED_HISTORY=0
    if [[ "$LOCAL_HAS_HEAD" -eq 1 ]] && git merge-base HEAD "origin/${DEFAULT_BRANCH}" >/dev/null 2>&1; then
      RELATED_HISTORY=1
    fi

    if [[ "$SOURCE_OF_TRUTH_MODE" -eq 1 ]]; then
      if [[ "$GIT_BOOTSTRAPPED" -eq 1 || "$RELATED_HISTORY" -eq 0 ]]; then
        warn "Adopting origin/${DEFAULT_BRANCH} as git base, then restoring current working tree as source of truth"

        clear_worktree_for_checkout

        git checkout -B "$DEFAULT_BRANCH" "origin/${DEFAULT_BRANCH}" >/dev/null 2>&1 || \
          die "Could not reset ${DEFAULT_BRANCH} to origin/${DEFAULT_BRANCH}"

        clear_worktree_for_checkout
        restore_worktree
      else
        ok "Related history detected; keeping current branch history"
      fi
    else
      if [[ "$RELATED_HISTORY" -eq 0 ]]; then
        die "Local history is unrelated to origin/${DEFAULT_BRANCH}. Clone ${OWNER}/${REPO} in this directory before running release.sh."
      fi
    fi
  else
    warn "origin/${DEFAULT_BRANCH} does not exist yet; current working tree will become initial ${DEFAULT_BRANCH} branch"
    clear_worktree_for_checkout
    restore_worktree
  fi

  [[ "$GIT_BOOTSTRAPPED" -eq 1 ]] && ok "Git repository bootstrapped"
  ok "Git ready"
else
  warn "Skipping git prep/fetch (git unavailable)"
fi

locate_source

# ==== VERSION =================================================================
step "Reading current version from ${MAIN_PATH}"
BASE_VER="$(read_current_version)"
[[ -n "$BASE_VER" ]] || BASE_VER="0.0.0"

if [[ "$GIT_OK" -eq 1 ]]; then
  latest_tag="$(git tag | grep -E '^v[0-9]+\.[0-9]+\.[0-9]+$' | sed 's/^v//' | sort -V | tail -n1 || true)"
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
  while tag_exists_local "$NEXT" || tag_exists_remote "$NEXT"; do
    ((PAT+=1))
    NEXT="${MAJ}.${MIN}.${PAT}"
  done
fi

step "Preparing release v${NEXT}"

# ==== FILE UPDATES ============================================================
step "Updating ${MAIN_PATH}"
update_main_file_version "$NEXT"
ok "Updated ${MAIN_PATH} to v${NEXT}"

update_changelog "$NEXT"

# ==== COMMIT / PUSH / TAG =====================================================
if [[ "$GIT_OK" -eq 1 ]]; then
  step "Committing changes"
  git add -A

  if ! git diff --cached --quiet; then
    git commit -m "chore(release): v${NEXT}" >/dev/null 2>&1
  else
    warn "Nothing changed to commit; continuing"
  fi

  step "Pushing ${DEFAULT_BRANCH} branch"

  pushed_main=0
  if [[ "$SOURCE_OF_TRUTH_MODE" -eq 1 ]]; then
    if git push --force-with-lease origin "$DEFAULT_BRANCH"; then
      pushed_main=1
    else
      warn "Force-with-lease push failed; refetching and retrying once"
      git fetch origin "$DEFAULT_BRANCH" --tags || git fetch origin --tags || true
      if git push --force-with-lease origin "$DEFAULT_BRANCH"; then
        pushed_main=1
      fi
    fi
  else
    if git push origin "$DEFAULT_BRANCH"; then
      pushed_main=1
    else
      warn "Push rejected; fetching origin/${DEFAULT_BRANCH}, rebasing, and retrying once"
      git fetch origin "$DEFAULT_BRANCH" --tags || git fetch origin --tags || true
      if git show-ref --verify --quiet "refs/remotes/origin/${DEFAULT_BRANCH}"; then
        if git rebase "origin/${DEFAULT_BRANCH}"; then
          if git push origin "$DEFAULT_BRANCH"; then
            pushed_main=1
          fi
        else
          warn "Auto-rebase failed; aborting rebase"
          git rebase --abort >/dev/null 2>&1 || true
        fi
      fi
    fi
  fi

  [[ "$pushed_main" -eq 1 ]] || die "Could not push ${DEFAULT_BRANCH}; aborting before tag/release."

  step "Tagging and pushing tag"
  tag_exists_local "$NEXT" && die "Tag v${NEXT} already exists locally"
  tag_exists_remote "$NEXT" && die "Tag v${NEXT} already exists on origin"

  git tag "v${NEXT}"
  git push origin "v${NEXT}" || die "Could not push tag v${NEXT}"

  ok "Git push complete"
else
  warn "Skipping commit/tag/push (git unavailable)"
fi

# ==== PACKAGE =================================================================
build_zip "$NEXT"

# ==== GITHUB RELEASE ==========================================================
publish_github_release "$NEXT"

ok "All done: artifacts/${PACKAGE_SLUG}-v${NEXT}.zip"
