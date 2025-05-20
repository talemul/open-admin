<?php

uses(\TestCase::class);
use Encore\Admin\Auth\Database\Administrator;
use Illuminate\Support\Facades\File;
use Tests\Models\File as FileModel;
beforeEach(function () {
    $this->be(Administrator::first(), 'admin');
});
test('file upload page', function () {
    $this->visit('admin/files/create')
        ->see('Files')
        ->seeInElement('h3[class=box-title]', 'Create')
        ->seeElement('input[name=file1]')
        ->seeElement('input[name=file2]')
        ->seeElement('input[name=file3]')
        ->seeElement('input[name=file4]')
        ->seeElement('input[name=file5]')
        ->seeElement('input[name=file6]')
//            ->seeInElement('a[href="/admin/files"]', 'List')
        ->seeInElement('button[type=reset]', 'Reset')
        ->seeInElement('button[type=submit]', 'Submit');
});
function uploadFiles()
{
    return $this->visit('admin/files/create')
        ->attach(__DIR__.'/AuthTest.php', 'file1')
        ->attach(__DIR__.'/InstallTest.php', 'file2')
        ->attach(__DIR__.'/IndexTest.php', 'file3')
        ->attach(__DIR__.'/LaravelTest.php', 'file4')
        ->attach(__DIR__.'/routes.php', 'file5')
        ->attach(__DIR__.'/migrations/2016_11_22_093148_create_test_tables.php', 'file6')
        ->press('Submit');
}
test('upload file', function () {
    File::cleanDirectory(public_path('uploads/files'));

    uploadFiles()
        ->seePageIs('admin/files');

    expect(1)->toEqual(FileModel::count());

    $where = [
        'file1' => 'files/AuthTest.php',
        'file2' => 'files/InstallTest.php',
        'file3' => 'files/IndexTest.php',
        'file4' => 'files/LaravelTest.php',
        'file5' => 'files/routes.php',
        'file6' => 'files/2016_11_22_093148_create_test_tables.php',
    ];

    $this->seeInDatabase('test_files', $where);

    $files = FileModel::first()->toArray();

    foreach (range(1, 6) as $index) {
        expect(public_path('uploads/'.$files['file'.$index]))->toBeFile();
    }

    File::cleanDirectory(public_path('uploads/files'));
});
test('update file', function () {
    File::cleanDirectory(public_path('uploads/files'));

    uploadFiles();

    $old = FileModel::first();

    $this->visit('admin/files/1/edit')
        ->see('ID')
        ->see('Created At')
        ->see('Updated At')
        ->seeElement('input[name=file1]')
        ->seeElement('input[name=file2]')
        ->seeElement('input[name=file3]')
        ->seeElement('input[name=file4]')
        ->seeElement('input[name=file5]')
        ->seeElement('input[name=file6]')
//            ->seeInElement('a[href="/admin/files"]', 'List')
        ->seeInElement('button[type=reset]', 'Reset')
        ->seeInElement('button[type=submit]', 'Submit');

    $this->attach(__DIR__.'/RolesTest.php', 'file3')
        ->attach(__DIR__.'/MenuTest.php', 'file4')
        ->attach(__DIR__.'/TestCase.php', 'file5')
        ->press('Submit');

    $new = FileModel::first();

    expect($new->id)->toEqual($old->id);
    expect($new->file1)->toEqual($old->file1);
    expect($new->file2)->toEqual($old->file2);
    expect($new->file6)->toEqual($old->file6);

    $this->assertNotEquals($old->file3, $new->file3);
    $this->assertNotEquals($old->file4, $new->file4);
    $this->assertNotEquals($old->file5, $new->file5);

    File::cleanDirectory(public_path('uploads/files'));
});
test('delete files', function () {
    File::cleanDirectory(public_path('uploads/files'));

    uploadFiles();

    $this->visit('admin/files')
        ->seeInElement('td', 1);

    $files = FileModel::first()->toArray();

    $this->delete('admin/files/1')
        ->dontSeeInDatabase('test_files', ['id' => 1]);

    foreach (range(1, 6) as $index) {
        $this->assertFileDoesNotExist(public_path('uploads/'.$files['file'.$index]));
    }

    $this->visit('admin/files')
        ->seeInElement('td', 'svg');
});
test('batch delete', function () {
    File::cleanDirectory(public_path('uploads/files'));

    uploadFiles();
    uploadFiles();
    uploadFiles();

    $this->visit('admin/files')
        ->seeInElement('td', 1)
        ->seeInElement('td', 2)
        ->seeInElement('td', 3);

    $fi = new FilesystemIterator(public_path('uploads/files'), FilesystemIterator::SKIP_DOTS);

    expect(18)->toEqual(iterator_count($fi));

    expect(3)->toEqual(FileModel::count());

    $this->delete('admin/files/1,2,3');

    expect(0)->toEqual(FileModel::count());

    $this->visit('admin/files')
        ->seeInElement('td', 'svg');

    expect(0)->toEqual(iterator_count($fi));
});
