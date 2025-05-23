@echo off
echo Running Pest Tests with SQLite...
docker compose run --rm -e DB_CONNECTION=sqlite -e DB_DATABASE=/var/www/database/database.sqlite app vendor/bin/pest
pause
