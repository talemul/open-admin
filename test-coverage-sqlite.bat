@echo off
echo Running Pest HTML coverage for SQLite...
docker compose run --rm -e DB_CONNECTION=sqlite -e DB_DATABASE=/var/www/database/database.sqlite app vendor/bin/pest --coverage-html coverage-sqlite
start coverage-sqlite\index.html
pause
