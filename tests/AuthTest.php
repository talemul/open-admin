<?php

uses(\TestCase::class);
test('login page', function () {
    $this->visit('admin/auth/login')
        ->see('login');
});

test('visit without login', function () {
    $this->visit('admin')
        ->dontSeeIsAuthenticated('admin')
        ->seePageIs('admin/auth/login');
});

test('login', function () {
    $credentials = ['username' => 'admin', 'password' => 'admin'];

    $this->visit('admin/auth/login')
        ->see('login')
        ->submitForm('Login', $credentials)
        ->see('dashboard')
        ->seeCredentials($credentials, 'admin')
        ->seeIsAuthenticated('admin')
        ->seePageIs('admin')
        ->see('Dashboard')
        ->see('Description...')

        ->see('Environment')
        ->see('PHP version')
        ->see('Laravel version')

        ->see('Available extensions')
        ->seeLink('laravel-admin-ext/helpers', 'https://github.com/laravel-admin-extensions/helpers')
        ->seeLink('laravel-admin-ext/backup', 'https://github.com/laravel-admin-extensions/backup')
        ->seeLink('laravel-admin-ext/media-manager', 'https://github.com/laravel-admin-extensions/media-manager')

        ->see('Dependencies')
        ->see('php')
//            ->see('>=7.0.0')
        ->see('laravel/framework');

    $this
        ->see('<span>Admin</span>')
        ->see('<span>Users</span>')
        ->see('<span>Roles</span>')
        ->see('<span>Permission</span>')
        ->see('<span>Operation log</span>')
        ->see('<span>Menu</span>');
});

test('logout', function () {
    $this->visit('admin/auth/logout')
        ->seePageIs('admin/auth/login')
        ->dontSeeIsAuthenticated('admin');
});
