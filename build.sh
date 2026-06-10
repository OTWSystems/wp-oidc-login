#!/bin/bash

# This script is used to build and package the plugin for distribution.
# It will clean up the environment, execute Composer, prefix dependencies, finalize the build, archive the build, and sign the archive.

function trim() {
    local str="$*"
    str="${str#"${str%%[![:space:]]*}"}"
    str="${str%"${str##*[![:space:]]}"}"
    echo "${str}"
}

function semver {
  local SEMVER_REGEX='^([0-9]+\.){2}(\*|[0-9]+)(-.*)?$'
  local version=$(trim $1)

  if [[ "$version" =~ $SEMVER_REGEX ]]; then
    if [ "$#" -eq 2 ]; then
      local major=${BASH_REMATCH[0]}
      local minor=${BASH_REMATCH[1]}
      local patch=${BASH_REMATCH[2]}
      local suffix=${BASH_REMATCH[3]}
      eval "$2=(\"$major\" \"$minor\" \"$patch\" \"$suffix\")"
    fi
  else
    echo "Error: version '$version' does not match the semver 'X.Y.Z' format."
    exit 1
  fi
}

printf 'What version are you building? '
read version
echo "Version: $version"
semver "$version" version
filename="WP_OIDC_Login_${version}.zip"

echo "# Cleaning up environment..."
rm -rf $filename
rm -f build.zip
rm -rf build
rm -rf wp-oidc-login/
rm -rf vendor

echo "# Executing Composer..."
composer update --no-plugins

echo "# Prefixing Dependencies..."
vendor/bin/php-scoper add-prefix --force

echo "# Finalizing Build..."
cd build && rm -rf vendor
composer update --no-dev --optimize-autoloader --no-plugins
rm -f composer.json
rm -f composer.lock
cd ..

echo "# Archiving Build..."
mv build/ wp-oidc-login/
zip -vr ${filename} wp-oidc-login/ -x "*.DS_Store"
