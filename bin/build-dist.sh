#!/usr/bin/env bash
#
# Assembles the distributable plugin directory consumed by CI — Plugin Check
# scans it, the release workflow zips it — and usable locally to inspect what
# a release would actually ship.
#
# Usage: bin/build-dist.sh [destination]
#
#   destination defaults to build/archives-widget-extended; relative paths are
#   resolved against the repository root.
#
# This is a deny-list: anything not excluded below ends up in the plugin zip.
# Any new development-only file added at the repository root needs an
# --exclude entry here, or it ships to users.
#
# Assumes `npm run build` (root and Block/) and `composer install --no-dev`
# have already run — this script only copies, it does not build.

set -euo pipefail

repo_root="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd -P)"
dest="${1:-build/archives-widget-extended}"

case "${dest}" in
	/*) ;;
	*) dest="${repo_root}/${dest}" ;;
esac

mkdir -p "${dest}"
# Canonicalise before the safety check below, so '.', 'build/..' and symlinked
# paths cannot slip past a plain string comparison.
dest="$(cd "${dest}" && pwd -P)"

# rsync --delete --delete-excluded wipes everything at the destination that is
# not part of the build, so refuse to point it at the working tree itself or at
# any directory containing it.
if [ "${dest}" = "/" ] || [ "${dest}" = "${repo_root}" ] || [ "${repo_root#"${dest}"/}" != "${repo_root}" ]; then
	echo "::error::Refusing to assemble the distributable into '${dest}': it is the repository root or a parent of it." >&2
	exit 1
fi

rsync -a --delete --delete-excluded \
	--include='/Block/' \
	--include='/Block/archives-widget-extended/' \
	--include='/Block/archives-widget-extended/build/***' \
	--exclude='/Block/archives-widget-extended/*' \
	--exclude='/.distignore' \
	--exclude='/.git' \
	--exclude='/.github' \
	--exclude='.claude' \
	--exclude='/.editorconfig' \
	--exclude='/.gitignore' \
	--exclude='/.idea' \
	--exclude='/.nvmrc' \
	--exclude='/.phpcs-cache' \
	--exclude='/.phpcs.xml.dist' \
	--exclude='/.phpunit.result.cache' \
	--exclude='/.wp-env.json' \
	--exclude='/bin' \
	--exclude='/composer.lock' \
	--exclude='/package.json' \
	--exclude='/package-lock.json' \
	--exclude='/phpcs.xml' \
	--exclude='/phpstan-bootstrap.php' \
	--exclude='/phpstan.neon' \
	--exclude='/phpunit.xml.dist' \
	--exclude='/postcss.config.js' \
	--exclude='/vite.config.js' \
	--exclude='/tests' \
	--exclude='/assets' \
	--include='/media/' \
	--include='/media/plugin/***' \
	--exclude='/media/*' \
	--exclude='/CHANGELOG.md' \
	--exclude='/CLAUDE.md' \
	--exclude='/INTEGRATION-*.md' \
	--exclude='/README.md' \
	--exclude='/build' \
	--exclude='node_modules' \
	--exclude='.DS_Store' \
	"${repo_root}/" "${dest}/"

echo "Assembled distributable in ${dest}"
