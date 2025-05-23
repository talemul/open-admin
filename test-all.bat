@echo off
echo Installing Composer dependencies...
docker compose run --rm app composer install

echo.
echo ================================================
echo Running Pest Tests with MySQL...
echo ================================================
docker compose run --rm ^
  -e DB_CONNECTION=mysql ^
  -e DB_HOST=mysql ^
  -e DB_PORT=3306 ^
  -e DB_DATABASE=open_admin_test ^
  -e DB_USERNAME=root ^
  -e DB_PASSWORD=root ^
  app vendor/bin/pest

echo.
echo ================================================
echo Running Pest Tests with PostgreSQL...
echo ================================================
docker compose run --rm ^
  -e DB_CONNECTION=pgsql ^
  -e DB_HOST=postgres ^
  -e DB_PORT=5432 ^
  -e DB_DATABASE=open_admin_test ^
  -e DB_USERNAME=root ^
  -e DB_PASSWORD=root ^
  app vendor/bin/pest

echo.
echo ================================================
echo Preparing SQLite database file...
echo ================================================
docker compose run --rm app sh -c "mkdir -p database && touch database/database.sqlite"

echo.
echo ================================================
echo Running Pest Tests with SQLite...
echo ================================================
docker compose run --rm ^
  -e DB_CONNECTION=sqlite ^
  -e DB_DATABASE=/var/www/database/database.sqlite ^
  app vendor/bin/pest

echo.
echo All tests completed across MySQL, PostgreSQL, and SQLite!
pause
