@echo off
echo Running Pest Test Suite with HTML Coverage...
docker compose run --rm -e PEST_ARGS="--coverage-html coverage-report" app vendor/bin/pest --coverage-html coverage-report
start coverage-report\index.html
pause
