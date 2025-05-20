<?php

uses(\TestCase::class);
test('installed directories', function () {
    expect(admin_path())->toBeFile();

    expect(admin_path('Controllers'))->toBeFile();

    expect(admin_path('routes.php'))->toBeFile();

    expect(admin_path('bootstrap.php'))->toBeFile();

    expect(admin_path('Controllers/HomeController.php'))->toBeFile();

    expect(admin_path('Controllers/AuthController.php'))->toBeFile();

    expect(admin_path('Controllers/ExampleController.php'))->toBeFile();

    expect(config_path('admin.php'))->toBeFile();

    expect(public_path('vendor/laravel-admin'))->toBeFile();
});
