<?php

uses(\TestCase::class);
use Encore\Admin\Auth\Database\Administrator;
use Encore\Admin\Auth\Database\Menu;
beforeEach(function () {
    $this->be(Administrator::first(), 'admin');
});
test('menu index', function () {
    $this->visit('admin/auth/menu')
        ->see('Menu')
        ->see('Index')
        ->see('Auth')
        ->see('Users')
        ->see('Roles')
        ->see('Permission')
        ->see('Menu');
});
test('add menu', function () {
    $item = ['parent_id' => '0', 'title' => 'Test', 'uri' => 'test'];

    $this->visit('admin/auth/menu')
        ->seePageIs('admin/auth/menu')
        ->see('Menu')
        ->submitForm('Submit', $item)
        ->seePageIs('admin/auth/menu')
        ->seeInDatabase(config('admin.database.menu_table'), $item)
        ->assertEquals(8, Menu::count());

    //        $this->expectException(\Laravel\BrowserKitTesting\HttpException::class);
    //
    //        $this->visit('admin')
    //            ->see('Test')
    //            ->click('Test');
});
test('delete menu', function () {
    $this->delete('admin/auth/menu/8')
        ->assertEquals(7, Menu::count());
});
test('edit menu', function () {
    $this->visit('admin/auth/menu/1/edit')
        ->see('Menu')
        ->submitForm('Submit', ['title' => 'blablabla'])
        ->seePageIs('admin/auth/menu')
        ->seeInDatabase(config('admin.database.menu_table'), ['title' => 'blablabla'])
        ->assertEquals(7, Menu::count());
});
test('show page', function () {
    $this->visit('admin/auth/menu/1/edit')
        ->seePageIs('admin/auth/menu/1/edit');
});
test('edit menu parent', function () {
    $this->expectException(\Laravel\BrowserKitTesting\HttpException::class);

    $this->visit('admin/auth/menu/5/edit')
        ->see('Menu')
        ->submitForm('Submit', ['parent_id' => 5]);
});
