#!/usr/bin/env bash
#
# Prints a wp-env `core` source string for the newest WordPress release that is
# actually downloadable right now, e.g.
#
#   https://wordpress.org/wordpress-7.0.4.zip
#
# Usage: bin/wp-core-source.sh
#
# Why this exists: wp-env's own "latest" handling (`"core": null`) asks
# api.wordpress.org which version is current, then fetches that version as a
# git tag from the WordPress/WordPress GitHub mirror. That mirror is synced by
# a bot and lags the announcement, so between a release and the sync every
# `wp-env start` dies with `fatal: couldn't find remote ref <version>`.
#
# The release zip on wordpress.org has no such gap — it is the release
# artifact — so we resolve the version ourselves and hand wp-env a zip source.
# The HEAD request below is the "is it really available" check: if the newest
# release somehow isn't downloadable, we step back through the previous ones
# instead of failing.
#
# Exits non-zero (printing nothing on stdout) when the version cannot be
# resolved at all — offline, or api.wordpress.org unreachable. Callers are
# expected to fall back to whatever `.wp-env.json` specifies.

set -euo pipefail

stable_check_url="https://api.wordpress.org/core/stable-check/1.0/"
download_url_prefix="https://wordpress.org/wordpress-"

# How far back to walk when the newest release is not downloadable yet.
max_candidates=3

# Every key in the stable-check payload is a released version, whatever its
# status ("insecure", "outdated", "latest"), so the highest of them is the
# current release. Sorting is more robust than trusting the payload's order.
releases="$(
	curl -fsS --max-time 15 "${stable_check_url}" \
		| grep -oE '"[0-9]+(\.[0-9]+)*"[[:space:]]*:' \
		| grep -oE '[0-9]+(\.[0-9]+)*' \
		| sort -Vr \
		| head -n "${max_candidates}"
)" || {
	echo "wp-core-source: could not reach ${stable_check_url}." >&2
	exit 1
}

if [ -z "${releases}" ]; then
	echo "wp-core-source: no WordPress versions found in the stable-check payload." >&2
	exit 1
fi

while IFS= read -r version; do
	url="${download_url_prefix}${version}.zip"

	# No -S here: a missing zip is an expected outcome this loop handles, and
	# curl's own error line would only duplicate the message below.
	if curl -fs --max-time 15 --head --output /dev/null "${url}"; then
		printf '%s\n' "${url}"
		exit 0
	fi

	echo "wp-core-source: WordPress ${version} is released but ${url} is not downloadable yet; trying the previous release." >&2
done <<< "${releases}"

echo "wp-core-source: none of the newest ${max_candidates} WordPress releases are downloadable." >&2
exit 1
