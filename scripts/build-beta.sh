#!/usr/bin/env bash
set -euo pipefail

ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
OUT="${1:-$ROOT/build}"
STAGE="$OUT/smg-site-suite"
ZIP="$OUT/smg-site-suite.zip"

rm -rf "$STAGE" "$ZIP"
mkdir -p "$STAGE" "$OUT"

copy() {
  local path="$1"
  if [[ -e "$ROOT/$path" ]]; then
    mkdir -p "$STAGE/$(dirname "$path")"
    cp -R "$ROOT/$path" "$STAGE/$path"
  fi
}

copy smg-site-suite.php
copy includes
copy src
copy assets
copy vendor/smg-wp-foundation/src
copy vendor/smg-wp-foundation/assets
copy README.md
copy CHANGELOG.md
copy docs

find "$STAGE" -type f \( -name '.DS_Store' -o -name '*.map' \) -delete
find "$STAGE" -type d -name '__MACOSX' -prune -exec rm -rf {} +

(
  cd "$OUT"
  zip -qr "smg-site-suite.zip" "smg-site-suite"
)

echo "$ZIP"
