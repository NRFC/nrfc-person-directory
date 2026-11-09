#!/bin/bash -e

DEFAULT_NAME=$(basename "$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)")
DEFAULT_NAME="${DEFAULT_NAME//[^a-zA-Z0-9]/_}"

read -r -p "Enter plugin name [$DEFAULT_NAME]: " USER_INPUT

PLUGIN_NAME="${USER_INPUT:-$DEFAULT_NAME}"
PLUGIN_NAME="${PLUGIN_NAME//[^a-zA-Z0-9]/_}"
VENDOR="${USER//[^a-zA-Z0-9]/_}"

grep -rq 'NEW_PLUGIN_NAME' . || exit 1
set -x
find . -type f \
    ! -name 'install.sh' \
    ! -name 'README.md' \
     -exec sed -i "s/NEW_PLUGIN_NAME/$PLUGIN_NAME/g" {} +
find . -type f \
    ! -name 'install.sh' \
    ! -name 'README.md' \
     -exec sed -i "s/VENDOR/$VENDOR/g" {} +
set +x

if command -v composer &> /dev/null; then
    echo "Composer found. Running composer install..."
    composer install
else
    echo "Composer not found, skipping dependency installation."
fi

if command -v node &> /dev/null; then
    export NVM_DIR="$([ -z "${XDG_CONFIG_HOME-}" ] && printf %s "${HOME}/.nvm" || printf %s "${XDG_CONFIG_HOME}/nvm")"
    [ -s "$NVM_DIR/nvm.sh" ] && \. "$NVM_DIR/nvm.sh"
    
    if command -v nvm &> /dev/null || [ -f "$NVM_DIR/nvm.sh" ]; then
        echo "NVM found. Switching Node version..."
        nvm use
    else
        echo "NVM not found, skipping Node version switch."
    fi
    
    if command -v yarn &> /dev/null; then
        echo "Yarn found. Running yarn install..."
        yarn install
    else
        echo "Yarn not found. Installing Yarn globally..."
        if command -v npm &> /dev/null; then
            npm install -g yarn
            yarn install
        else
            echo "npm not found, cannot install Yarn. Skipping JavaScript dependency installation."
        fi
    fi
else
    echo "Node not found, skipping set up"
fi

echo "Successfully initialized plugin as: $PLUGIN_NAME"
