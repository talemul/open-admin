<?php

uses(\TestCase::class);
use Encore\Admin\Auth\Database\Administrator;
use Tests\Models\Profile as ProfileModel;
use Tests\Models\User as UserModel;
beforeEach(function () {
    $this->be(Administrator::first(), 'admin');
});
test('index page', function () {
    $this->visit('admin/users')
        ->see('Users')
        ->seeInElement('tr th', 'Username')
        ->seeInElement('tr th', 'Email')
        ->seeInElement('tr th', 'Mobile')
        ->seeInElement('tr th', 'Full name')
        ->seeInElement('tr th', 'Avatar')
        ->seeInElement('tr th', 'Post code')
        ->seeInElement('tr th', 'Address')
        ->seeInElement('tr th', 'Position')
        ->seeInElement('tr th', 'Color')
        ->seeInElement('tr th', '开始时间')
        ->seeInElement('tr th', '结束时间')
        ->seeInElement('tr th', 'Color')
        ->seeInElement('tr th', 'Created at')
        ->seeInElement('tr th', 'Updated at');

    $action = url('/admin/users');

    $this->seeElement("form[action='$action'][method=get]")
        ->seeElement("form[action='$action'][method=get] input[name=id]")
        ->seeElement("form[action='$action'][method=get] input[name=username]")
        ->seeElement("form[action='$action'][method=get] input[name=email]")
        ->seeElement("form[action='$action'][method=get] input[name='profile[start_at][start]']")
        ->seeElement("form[action='$action'][method=get] input[name='profile[start_at][end]']")
        ->seeElement("form[action='$action'][method=get] input[name='profile[end_at][start]']")
        ->seeElement("form[action='$action'][method=get] input[name='profile[end_at][end]']");

    $urlAll = url('/admin/users?_export_=all');
    $urlNew = url('/admin/users/create');
    $this->seeInElement("a[href=\"{$urlAll}\"]", 'All')
        ->seeInElement("a[href=\"{$urlNew}\"]", 'New');
});
function seedsTable($count = 100)
{
    factory(\Tests\Models\User::class, $count)
        ->create()
        ->each(function ($u) {
            $u->profile()->save(factory(\Tests\Models\Profile::class)->make());
            $u->tags()->saveMany(factory(\Tests\Models\Tag::class, 5)->make());
            $u->data = ['json' => ['field' => random_int(0, 50)]];
            $u->save();
        });
}
test('grid with data', function () {
    seedsTable();

    $this->visit('admin/users')
        ->see('Users');

    expect(UserModel::all())->toHaveCount(100);
    expect(ProfileModel::all())->toHaveCount(100);
});
test('grid pagination', function () {
    seedsTable(65);

    $this->visit('admin/users')
        ->see('Users');

    $this->visit('admin/users?page=2');
    expect($this->crawler()->filter('td a i[class*=fa-edit]'))->toHaveCount(20);

    $this->visit('admin/users?page=3');
    expect($this->crawler()->filter('td a i[class*=fa-edit]'))->toHaveCount(20);

    $this->visit('admin/users?page=4');
    expect($this->crawler()->filter('td a i[class*=fa-edit]'))->toHaveCount(5);

    $this->click(1)->seePageIs('admin/users?page=1');
    expect($this->crawler()->filter('td a i[class*=fa-edit]'))->toHaveCount(20);
});
test('order by json', function () {
    seedsTable(10);
    expect(UserModel::all())->toHaveCount(10);

    $this->visit('admin/users?_sort[column]=data.json.field&_sort[type]=desc&_sort[cast]=unsigned');

    $jsonTds = $this->crawler->filter('table.table tbody td.column-data-json-field');
    expect($jsonTds)->toHaveCount(10);
    $prevValue = PHP_INT_MAX;
    foreach ($jsonTds as $jsonTd) {
        $currentValue = (int) $jsonTd->nodeValue;
        expect($currentValue <= $prevValue)->toBeTrue();
        $prevValue = $currentValue;
    }
});
test('equal filter', function () {
    seedsTable(50);

    $this->visit('admin/users')
        ->see('Users');

    expect(UserModel::all())->toHaveCount(50);
    expect(ProfileModel::all())->toHaveCount(50);

    $id = rand(1, 50);

    $user = UserModel::find($id);

    $this->visit('admin/users?id='.$id)
        ->seeInElement('td', $user->username)
        ->seeInElement('td', $user->email)
        ->seeInElement('td', $user->mobile)
        ->seeElement("img[src='{$user->avatar}']")
        ->seeInElement('td', "{$user->profile->first_name} {$user->profile->last_name}")
        ->seeInElement('td', $user->postcode)
        ->seeInElement('td', $user->address)
        ->seeInElement('td', "{$user->profile->latitude} {$user->profile->longitude}")
        ->seeInElement('td', $user->color)
        ->seeInElement('td', $user->start_at)
        ->seeInElement('td', $user->end_at);
});
test('like filter', function () {
    seedsTable(50);

    $this->visit('admin/users')
        ->see('Users');

    expect(UserModel::all())->toHaveCount(50);
    expect(ProfileModel::all())->toHaveCount(50);

    $users = UserModel::where('username', 'like', '%mi%')->get();

    $this->visit('admin/users?username=mi');

    expect($users)->toHaveCount($this->crawler()->filter('table tr')->count() - 1);

    foreach ($users as $user) {
        $this->seeInElement('td', $user->username);
    }
});
test('filter relation', function () {
    seedsTable(50);

    $user = UserModel::with('profile')->find(rand(1, 50));

    $this->visit('admin/users?email='.$user->email)
        ->seeInElement('td', $user->username)
        ->seeInElement('td', $user->email)
        ->seeInElement('td', $user->mobile)
        ->seeElement("img[src='{$user->avatar}']")
        ->seeInElement('td', "{$user->profile->first_name} {$user->profile->last_name}")
        ->seeInElement('td', $user->postcode)
        ->seeInElement('td', $user->address)
        ->seeInElement('td', "{$user->profile->latitude} {$user->profile->longitude}")
        ->seeInElement('td', $user->color)
        ->seeInElement('td', $user->start_at)
        ->seeInElement('td', $user->end_at);
});
test('display callback', function () {
    seedsTable(1);

    $user = UserModel::with('profile')->find(1);

    $this->visit('admin/users')
        ->seeInElement('th', 'Column1 not in table')
        ->seeInElement('th', 'Column2 not in table')
        ->seeInElement('td', "full name:{$user->profile->first_name} {$user->profile->last_name}")
        ->seeInElement('td', "{$user->email}#{$user->profile->color}");
});
test('has many relation', function () {
    factory(\Tests\Models\User::class, 10)
        ->create()
        ->each(function ($u) {
            $u->profile()->save(factory(\Tests\Models\Profile::class)->make());
            $u->tags()->saveMany(factory(\Tests\Models\Tag::class, 5)->make());
        });

    $this->visit('admin/users')
        ->seeElement('td code');

    expect($this->crawler()->filter('td code'))->toHaveCount(50);
});
test('grid actions', function () {
    seedsTable(15);

    $this->visit('admin/users');

    expect($this->crawler()->filter('td a i[class*=fa-edit]'))->toHaveCount(15);
    expect($this->crawler()->filter('td a i[class*=fa-trash]'))->toHaveCount(15);
});
test('grid rows', function () {
    seedsTable(10);

    $this->visit('admin/users')
        ->seeInElement('td a[class*=btn]', 'detail');

    expect($this->crawler()->filter('td a[class*=btn]'))->toHaveCount(5);
});
test('grid per page', function () {
    seedsTable(98);

    $this->visit('admin/users')
        ->seeElement('select[class*=per-page][name=per-page]')
        ->seeInElement('select option', 10)
        ->seeInElement('select option[selected]', 20)
        ->seeInElement('select option', 30)
        ->seeInElement('select option', 50)
        ->seeInElement('select option', 100);

    expect($this->crawler()->filter('select option[selected]')->attr('value'))->toEqual('http://localhost:8000/admin/users?per_page=20');

    $perPage = rand(1, 98);

    $this->visit('admin/users?per_page='.$perPage)
        ->seeInElement('select option[selected]', $perPage)
        ->assertCount($perPage + 1, $this->crawler()->filter('tr'));
});
