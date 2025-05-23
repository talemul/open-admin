@echo off
echo Cleaning up previous coverage files...
rmdir /S /Q coverage-mysql 2>nul
rmdir /S /Q coverage-pgsql 2>nul
rmdir /S /Q coverage-sqlite 2>nul
rmdir /S /Q coverage-report 2>nul

echo ================================================
echo Running Pest Tests with MySQL...
echo ================================================
docker compose run --rm -e DB_CONNECTION=mysql -e DB_HOST=mysql -e DB_PORT=3307 -e DB_DATABASE=testing -e DB_USERNAME=root -e DB_PASSWORD=root -e PEST_ARGS="--coverage-html coverage-mysql" app vendor/bin/pest --coverage-html coverage-mysql

echo ================================================
echo Running Pest Tests with PostgreSQL...
echo ================================================
docker compose run --rm -e DB_CONNECTION=pgsql -e DB_HOST=pgsql -e DB_PORT=5433 -e DB_DATABASE=testing -e DB_USERNAME=postgres -e DB_PASSWORD=postgres -e PEST_ARGS="--coverage-html coverage-pgsql" app vendor/bin/pest --coverage-html coverage-pgsql

echo ================================================
echo Running Pest Tests with SQLite...
echo ================================================
docker compose run --rm -e DB_CONNECTION=sqlite -e DB_DATABASE=/var/www/database/database.sqlite -e PEST_ARGS="--coverage-html coverage-sqlite" app vendor/bin/pest --coverage-html coverage-sqlite

echo ================================================
echo Merging Coverage...
echo ================================================
php vendor\bin\phpcov merge coverage-mysql coverage-pgsql coverage-sqlite --html coverage-report

echo Opening combined coverage report...
start coverage-report\index.html

echo Done
pause
