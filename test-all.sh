#!/bin/bash

set -e

echo "🔄 Installing composer dependencies..."
docker-compose run --rm app composer install

echo "🔍 Running Pest Tests with MySQL..."
cp .env.mysql .env
docker-compose run --rm app vendor/bin/pest --coverage

echo "🔍 Running Pest Tests with PostgreSQL..."
cp .env.pgsql .env
docker-compose run --rm app vendor/bin/pest --coverage

echo "🔍 Running Pest Tests with SQLite..."
cp .env.sqlite .env
docker-compose run --rm app vendor/bin/pest --coverage

echo "✅ All tests completed across MySQL, PostgreSQL, and SQLite!"