<?php

uses(\TestCase::class);
use Encore\Admin\Auth\Database\Administrator;
use Encore\Admin\Auth\Database\Role;
beforeEach(function () {
    $this->be(Administrator::first(), 'admin');
});
test('roles index', function () {
    $this->visit('admin/auth/roles')
        ->see('Roles')
        ->see('administrator');
});
test('add role', function () {
    $this->visit('admin/auth/roles/create')
        ->see('Roles')
        ->submitForm('Submit', ['slug' => 'developer', 'name' => 'Developer...'])
        ->seePageIs('admin/auth/roles')
        ->seeInDatabase(config('admin.database.roles_table'), ['slug' => 'developer', 'name' => 'Developer...'])
        ->assertEquals(2, Role::count());
});
test('add role to user', function () {
    $user = [
        'username'              => 'Test',
        'name'                  => 'Name',
        'password'              => '123456',
        'password_confirmation' => '123456',

    ];

    $this->visit('admin/auth/users/create')
        ->see('Create')
        ->submitForm('Submit', $user)
        ->seePageIs('admin/auth/users')
        ->seeInDatabase(config('admin.database.users_table'), ['username' => 'Test']);

    expect(Role::count())->toEqual(1);

    $this->visit('admin/auth/roles/create')
        ->see('Roles')
        ->submitForm('Submit', ['slug' => 'developer', 'name' => 'Developer...'])
        ->seePageIs('admin/auth/roles')
        ->seeInDatabase(config('admin.database.roles_table'), ['slug' => 'developer', 'name' => 'Developer...'])
        ->assertEquals(2, Role::count());

    expect(Administrator::find(2)->isRole('developer'))->toBeFalse();

    $this->visit('admin/auth/users/2/edit')
        ->see('Edit')
        ->submitForm('Submit', ['roles' => [2]])
        ->seePageIs('admin/auth/users')
        ->seeInDatabase(config('admin.database.role_users_table'), ['user_id' => 2, 'role_id' => 2]);

    expect(Administrator::find(2)->isRole('developer'))->toBeTrue();

    expect(Administrator::find(2)->inRoles(['editor', 'operator']))->toBeFalse();
    expect(Administrator::find(2)->inRoles(['developer', 'operator', 'editor']))->toBeTrue();
});
test('delete role', function () {
    expect(Role::count())->toEqual(1);

    $this->visit('admin/auth/roles/create')
        ->see('Roles')
        ->submitForm('Submit', ['slug' => 'developer', 'name' => 'Developer...'])
        ->seePageIs('admin/auth/roles')
        ->seeInDatabase(config('admin.database.roles_table'), ['slug' => 'developer', 'name' => 'Developer...'])
        ->assertEquals(2, Role::count());

    $this->delete('admin/auth/roles/2')
        ->assertEquals(1, Role::count());

    $this->delete('admin/auth/roles/1')
        ->assertEquals(0, Role::count());
});
test('edit role', function () {
    $this->visit('admin/auth/roles/create')
        ->see('Roles')
        ->submitForm('Submit', ['slug' => 'developer', 'name' => 'Developer...'])
        ->seePageIs('admin/auth/roles')
        ->seeInDatabase(config('admin.database.roles_table'), ['slug' => 'developer', 'name' => 'Developer...'])
        ->assertEquals(2, Role::count());

    $this->visit('admin/auth/roles/2/edit')
        ->see('Roles')
        ->submitForm('Submit', ['name' => 'blablabla'])
        ->seePageIs('admin/auth/roles')
        ->seeInDatabase(config('admin.database.roles_table'), ['name' => 'blablabla'])
        ->assertEquals(2, Role::count());
});
