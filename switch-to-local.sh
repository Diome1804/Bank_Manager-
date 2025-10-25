#!/bin/bash
cp .env.local.backup .env
echo "💻 Switched to local environment (without Docker)"
echo "DB_HOST=localhost, DB_PORT=5433"
