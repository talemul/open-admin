<?php

uses(\TestCase::class);
use Encore\Admin\Auth\Database\Administrator;
use Encore\Admin\Auth\Database\Permission;
use Encore\Admin\Auth\Database\Role;
beforeEach(function () {
    $this->be(Administrator::first(), 'admin');
});
test('permissions index', function () {
    expect(Administrator::first()->isAdministrator())->toBeTrue();

    $this->visit('admin/auth/permissions')
        ->see('Permissions');
});
test('add and delete permissions', function () {
    $this->visit('admin/auth/permissions/create')
        ->see('Permissions')
        ->submitForm('Submit', ['slug' => 'can-edit', 'name' => 'Can edit', 'http_path' => 'users/1/edit', 'http_method' => ['GET']])
        ->seePageIs('admin/auth/permissions')
        ->visit('admin/auth/permissions/create')
        ->see('Permissions')
        ->submitForm('Submit', ['slug' => 'can-delete', 'name' => 'Can delete', 'http_path' => 'users/1', 'http_method' => ['DELETE']])
        ->seePageIs('admin/auth/permissions')
        ->seeInDatabase(config('admin.database.permissions_table'), ['slug' => 'can-edit', 'name' => 'Can edit', 'http_path' => 'users/1/edit', 'http_method' => 'GET'])
        ->seeInDatabase(config('admin.database.permissions_table'), ['slug' => 'can-delete', 'name' => 'Can delete', 'http_path' => 'users/1', 'http_method' => 'DELETE'])
        ->assertEquals(7, Permission::count());

    expect(Administrator::first()->can('can-edit'))->toBeTrue();
    expect(Administrator::first()->can('can-delete'))->toBeTrue();

    $this->delete('admin/auth/permissions/6')
        ->assertEquals(6, Permission::count());

    $this->delete('admin/auth/permissions/7')
        ->assertEquals(5, Permission::count());
});
test('add permission to role', function () {
    $this->visit('admin/auth/permissions/create')
        ->see('Permissions')
        ->submitForm('Submit', ['slug' => 'can-create', 'name' => 'Can Create', 'http_path' => 'users/create', 'http_method' => ['GET']])
        ->seePageIs('admin/auth/permissions');

    expect(Permission::count())->toEqual(6);

    $this->visit('admin/auth/roles/1/edit')
        ->see('Edit')
        ->submitForm('Submit', ['permissions' => [1]])
        ->seePageIs('admin/auth/roles')
        ->seeInDatabase(config('admin.database.role_permissions_table'), ['role_id' => 1, 'permission_id' => 1]);
});
test('add permission to user', function () {
    $this->visit('admin/auth/permissions/create')
        ->see('Permissions')
        ->submitForm('Submit', ['slug' => 'can-create', 'name' => 'Can Create', 'http_path' => 'users/create', 'http_method' => ['GET']])
        ->seePageIs('admin/auth/permissions');

    expect(Permission::count())->toEqual(6);

    $this->visit('admin/auth/users/1/edit')
        ->see('Edit')
        ->submitForm('Submit', ['permissions' => [1], 'roles' => [1]])
        ->seePageIs('admin/auth/users')
        ->seeInDatabase(config('admin.database.user_permissions_table'), ['user_id' => 1, 'permission_id' => 1])
        ->seeInDatabase(config('admin.database.role_users_table'), ['user_id' => 1, 'role_id' => 1]);
});
test('add user and assign permission', function () {
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

    expect(Administrator::find(2)->isAdministrator())->toBeFalse();

    $this->visit('admin/auth/permissions/create')
        ->see('Permissions')
        ->submitForm('Submit', ['slug' => 'can-update', 'name' => 'Can Update', 'http_path' => 'users/*/edit', 'http_method' => ['GET']])
        ->seePageIs('admin/auth/permissions');

    expect(Permission::count())->toEqual(6);

    $this->visit('admin/auth/permissions/create')
        ->see('Permissions')
        ->submitForm('Submit', ['slug' => 'can-remove', 'name' => 'Can Remove', 'http_path' => 'users/*', 'http_method' => ['DELETE']])
        ->seePageIs('admin/auth/permissions');

    expect(Permission::count())->toEqual(7);

    $this->visit('admin/auth/users/2/edit')
        ->see('Edit')
        ->submitForm('Submit', ['permissions' => [6]])
        ->seePageIs('admin/auth/users')
        ->seeInDatabase(config('admin.database.user_permissions_table'), ['user_id' => 2, 'permission_id' => 6]);

    expect(Administrator::find(2)->can('can-update'))->toBeTrue();
    expect(Administrator::find(2)->cannot('can-remove'))->toBeTrue();

    $this->visit('admin/auth/users/2/edit')
        ->see('Edit')
        ->submitForm('Submit', ['permissions' => [7]])
        ->seePageIs('admin/auth/users')
        ->seeInDatabase(config('admin.database.user_permissions_table'), ['user_id' => 2, 'permission_id' => 7]);

    expect(Administrator::find(2)->can('can-remove'))->toBeTrue();

    $this->visit('admin/auth/users/2/edit')
        ->see('Edit')
        ->submitForm('Submit', ['permissions' => []])
        ->seePageIs('admin/auth/users')
        ->missingFromDatabase(config('admin.database.user_permissions_table'), ['user_id' => 2, 'permission_id' => 6])
        ->missingFromDatabase(config('admin.database.user_permissions_table'), ['user_id' => 2, 'permission_id' => 7]);

    expect(Administrator::find(2)->cannot('can-update'))->toBeTrue();
    expect(Administrator::find(2)->cannot('can-remove'))->toBeTrue();
});
test('permission through role', function () {
    $user = [
        'username'              => 'Test',
        'name'                  => 'Name',
        'password'              => '123456',
        'password_confirmation' => '123456',
    ];

    // 1.add a user
    $this->visit('admin/auth/users/create')
        ->see('Create')
        ->submitForm('Submit', $user)
        ->seePageIs('admin/auth/users')
        ->seeInDatabase(config('admin.database.users_table'), ['username' => 'Test']);

    expect(Administrator::find(2)->isAdministrator())->toBeFalse();

    // 2.add a role
    $this->visit('admin/auth/roles/create')
        ->see('Roles')
        ->submitForm('Submit', ['slug' => 'developer', 'name' => 'Developer...'])
        ->seePageIs('admin/auth/roles')
        ->seeInDatabase(config('admin.database.roles_table'), ['slug' => 'developer', 'name' => 'Developer...'])
        ->assertEquals(2, Role::count());

    expect(Administrator::find(2)->isRole('developer'))->toBeFalse();

    // 3.assign role to user
    $this->visit('admin/auth/users/2/edit')
        ->see('Edit')
        ->submitForm('Submit', ['roles' => [2]])
        ->seePageIs('admin/auth/users')
        ->seeInDatabase(config('admin.database.role_users_table'), ['user_id' => 2, 'role_id' => 2]);

    expect(Administrator::find(2)->isRole('developer'))->toBeTrue();

    //  4.add a permission
    $this->visit('admin/auth/permissions/create')
        ->see('Permissions')
        ->submitForm('Submit', ['slug' => 'can-remove', 'name' => 'Can Remove', 'http_path' => 'users/*', 'http_method' => ['DELETE']])
        ->seePageIs('admin/auth/permissions');

    expect(Permission::count())->toEqual(6);

    expect(Administrator::find(2)->cannot('can-remove'))->toBeTrue();

    // 5.assign permission to role
    $this->visit('admin/auth/roles/2/edit')
        ->see('Edit')
        ->submitForm('Submit', ['permissions' => [6]])
        ->seePageIs('admin/auth/roles')
        ->seeInDatabase(config('admin.database.role_permissions_table'), ['role_id' => 2, 'permission_id' => 6]);

    expect(Administrator::find(2)->can('can-remove'))->toBeTrue();
});
test('edit permission', function () {
    $this->visit('admin/auth/permissions/create')
        ->see('Permissions')
        ->submitForm('Submit', ['slug' => 'can-edit', 'name' => 'Can edit', 'http_path' => 'users/1/edit', 'http_method' => ['GET']])
        ->seePageIs('admin/auth/permissions')
        ->seeInDatabase(config('admin.database.permissions_table'), ['slug' => 'can-edit'])
        ->seeInDatabase(config('admin.database.permissions_table'), ['name' => 'Can edit'])
        ->assertEquals(6, Permission::count());

    $this->visit('admin/auth/permissions/1/edit')
        ->see('Permissions')
        ->submitForm('Submit', ['slug' => 'can-delete'])
        ->seePageIs('admin/auth/permissions')
        ->seeInDatabase(config('admin.database.permissions_table'), ['slug' => 'can-delete'])
        ->assertEquals(6, Permission::count());
});
