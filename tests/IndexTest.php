<?php

uses(\TestCase::class);
use Encore\Admin\Auth\Database\Administrator;
beforeEach(function () {
    $this->be(Administrator::first(), 'admin');
});
test('index', function () {
    $this->visit('admin/')
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
});
test('click menu', function () {
    $this->visit('admin/')
        ->click('Users')
        ->seePageis('admin/auth/users')
        ->click('Roles')
        ->seePageis('admin/auth/roles')
        ->click('Permission')
        ->seePageis('admin/auth/permissions')
        ->click('Menu')
        ->seePageis('admin/auth/menu')
        ->click('Operation log')
        ->seePageis('admin/auth/logs');
});
