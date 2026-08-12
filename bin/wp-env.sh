#!/usr/bin/env bash
#
# Runs wp-env with `core` pinned to the newest downloadable WordPress release
# (see bin/wp-core-source.sh for why wp-env's own "latest" is unreliable).
#
# Usage: bin/wp-env.sh <wp-env arguments>
#
# WP_ENV_CORE overrides the `core` value of every environment in
# .wp-env.json, so every wp-env invocation of a session must go through here —
# a `start` pinned to a zip source and a `run` falling back to the config would
# be pointing at two different WordPress installs.
#
# An already-set WP_ENV_CORE is left alone, so a specific version can still be
# forced: WP_ENV_CORE=WordPress/WordPress#6.9.7 npm run wp-env:start

set -euo pipefail

repo_root="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd -P)"

if [ -z "${WP_ENV_CORE:-}" ]; then
	# Resolution needs the network. When it fails, carry on without the
	# override: wp-env then uses `core` from .wp-env.json, which falls back to
	# its own on-disk version cache when offline.
	if core="$( "${repo_root}/bin/wp-core-source.sh" )"; then
		export WP_ENV_CORE="${core}"
	fi
fi

if [ -n "${WP_ENV_CORE:-}" ]; then
	echo "wp-env core source: ${WP_ENV_CORE}" >&2
fi

exec npx wp-env "$@"
