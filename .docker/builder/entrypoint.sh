#!/bin/sh
export NODE_OPTIONS=--openssl-legacy-provider
set -e

cd /app
yarn install --network-timeout 1000000 --frozen-lockfile

/usr/bin/supervisord
