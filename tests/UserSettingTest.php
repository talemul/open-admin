<?php

uses(\TestCase::class);
use Encore\Admin\Auth\Database\Administrator;
use Illuminate\Support\Facades\File;
beforeEach(function () {
    $this->be(Administrator::first(), 'admin');
});
test('visit setting page', function () {
    $this->visit('admin/auth/setting')
        ->see('User setting')
        ->see('Username')
        ->see('Name')
        ->see('Avatar')
        ->see('Password')
        ->see('Password confirmation');

    $this->seeElement('input[value=Administrator]')
        ->seeInElement('.box-body', 'administrator');
});
test('update name', function () {
    $data = [
        'name' => 'tester',
    ];

    $this->visit('admin/auth/setting')
        ->submitForm('Submit', $data)
        ->seePageIs('admin/auth/setting');

    $this->seeInDatabase('admin_users', ['name' => $data['name']]);
});
test('update avatar', function () {
    File::cleanDirectory(public_path('uploads/images'));

    $this->visit('admin/auth/setting')
        ->attach(__DIR__.'/assets/test.jpg', 'avatar')
        ->press('Submit')
        ->seePageIs('admin/auth/setting');

    $avatar = Administrator::first()->avatar;

    expect($avatar)->toEqual('http://localhost:8000/uploads/images/test.jpg');
});
test('update password confirmation', function () {
    $data = [
        'password'              => '123456',
        'password_confirmation' => '123',
    ];

    $this->visit('admin/auth/setting')
        ->submitForm('Submit', $data)
        ->seePageIs('admin/auth/setting')
        ->see('The Password confirmation does not match.');
});
test('update password', function () {
    $data = [
        'password'              => '123456',
        'password_confirmation' => '123456',
    ];

    $this->visit('admin/auth/setting')
        ->submitForm('Submit', $data)
        ->seePageIs('admin/auth/setting');

    expect(app('hash')->check($data['password'], Administrator::first()->makeVisible('password')->password))->toBeTrue();

    $this->visit('admin/auth/logout')
        ->seePageIs('admin/auth/login')
        ->dontSeeIsAuthenticated('admin');

    $credentials = ['username' => 'admin', 'password' => '123456'];

    $this->visit('admin/auth/login')
        ->see('login')
        ->submitForm('Login', $credentials)
        ->see('dashboard')
        ->seeCredentials($credentials, 'admin')
        ->seeIsAuthenticated('admin')
        ->seePageIs('admin');
});
