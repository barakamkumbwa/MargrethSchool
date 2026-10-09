#!/usr/bin/env bash
set -e
cd "$(dirname "$0")"
exec php -S 127.0.0.1:8000 -t .
