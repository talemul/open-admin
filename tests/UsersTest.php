<?php

uses(\TestCase::class);
use Encore\Admin\Auth\Database\Administrator;
beforeEach(function () {
    $this->user = Administrator::first();

    $this->be($this->user, 'admin');
});
test('users index page', function () {
    $this->visit('admin/auth/users')
        ->see('Administrator');
});
test('create user', function () {
    $user = [
        'username'              => 'Test',
        'name'                  => 'Name',
        'password'              => '123456',
        'password_confirmation' => '123456',
    ];

    // create user
    $this->visit('admin/auth/users/create')
        ->see('Create')
        ->submitForm('Submit', $user)
        ->seePageIs('admin/auth/users')
        ->seeInDatabase(config('admin.database.users_table'), ['username' => 'Test']);

    // assign role to user
    $this->visit('admin/auth/users/2/edit')
        ->see('Edit')
        ->submitForm('Submit', ['roles' => [1]])
        ->seePageIs('admin/auth/users')
        ->seeInDatabase(config('admin.database.role_users_table'), ['user_id' => 2, 'role_id' => 1]);

    $this->visit('admin/auth/logout')
        ->dontSeeIsAuthenticated('admin')
        ->seePageIs('admin/auth/login')
        ->submitForm('Login', ['username' => $user['username'], 'password' => $user['password']])
        ->see('dashboard')
        ->seeIsAuthenticated('admin')
        ->seePageIs('admin');

    expect($this->app['auth']->guard('admin')->getUser()->isAdministrator())->toBeTrue();

    $this->see('<span>Users</span>')
        ->see('<span>Roles</span>')
        ->see('<span>Permission</span>')
        ->see('<span>Operation log</span>')
        ->see('<span>Menu</span>');
});
test('update user', function () {
    $this->visit('admin/auth/users/'.$this->user->id.'/edit')
        ->see('Create')
        ->submitForm('Submit', ['name' => 'test', 'roles' => [1]])
        ->seePageIs('admin/auth/users')
        ->seeInDatabase(config('admin.database.users_table'), ['name' => 'test']);
});
test('reset password', function () {
    $password = 'odjwyufkglte';

    $data = [
        'password'              => $password,
        'password_confirmation' => $password,
        'roles'                 => [1],
    ];

    $this->visit('admin/auth/users/'.$this->user->id.'/edit')
        ->see('Create')
        ->submitForm('Submit', $data)
        ->seePageIs('admin/auth/users')
        ->visit('admin/auth/logout')
        ->dontSeeIsAuthenticated('admin')
        ->seePageIs('admin/auth/login')
        ->submitForm('Login', ['username' => $this->user->username, 'password' => $password])
        ->see('dashboard')
        ->seeIsAuthenticated('admin')
        ->seePageIs('admin');
});
