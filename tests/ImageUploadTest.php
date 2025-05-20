<?php

uses(TestCase::class);
use Illuminate\Support\Facades\File;
use OpenAdmin\Admin\Auth\Database\Administrator;
use Tests\Models\Image;
use Tests\Models\MultipleImage;
use Tests\TestCase;

beforeEach(function () {
    $this->be(Administrator::first(), 'admin');
});
test('disable filter', function () {
    $this->visit('admin/images')
        ->dontSeeElement('input[name=id]');
});
test('image upload page', function () {
    $this->visit('admin/images/create')
        ->see('Images')
        ->seeInElement('h3[class=box-title]', 'Create')
        ->seeElement('input[name=image1]')
        ->seeElement('input[name=image2]')
        ->seeElement('input[name=image3]')
        ->seeElement('input[name=image4]')
        ->seeElement('input[name=image5]')
        ->seeElement('input[name=image6]')
        ->seeInElement('button[type=reset]', 'Reset')
        ->seeInElement('button[type=submit]', 'Submit');
});
function uploadImages()
{
//    return $this->visit('admin/images/create')
//        ->attach(__DIR__.'/assets/test.jpg', 'image1')
//        ->attach(__DIR__.'/assets/test.jpg', 'image2')
//        ->attach(__DIR__.'/assets/test.jpg', 'image3')
//        ->attach(__DIR__.'/assets/test.jpg', 'image4')
//        ->attach(__DIR__.'/assets/test.jpg', 'image5')
//        ->attach(__DIR__.'/assets/test.jpg', 'image6')
//        ->press('Submit');
}
test('upload image', function () {
    File::cleanDirectory(public_path('uploads/images'));

    uploadImages()
        ->seePageIs('admin/images');

    expect(1)->toEqual(Image::count());

    $this->seeInDatabase('test_images', ['image4' => 'images/renamed.jpeg']);

    $images = Image::first()->toArray();

    foreach (range(1, 6) as $index) {
        expect(public_path('uploads/'.$images['image'.$index]))->toBeFile();
    }

    expect(public_path('uploads/images/asdasdasdasdasd.jpeg'))->toBeFile();

    File::cleanDirectory(public_path('uploads/images'));
});
test('remove image', function () {
    File::cleanDirectory(public_path('uploads/images'));

    uploadImages();

    expect(6)->toEqual(fileCountInImageDir());
});
test('update image', function () {
    File::cleanDirectory(public_path('uploads/images'));

    uploadImages();

    $old = Image::first();

    $this->visit('admin/images/1/edit')
        ->see('ID')
        ->see('Created At')
        ->see('Updated At')
        ->seeElement('input[name=image1]')
        ->seeElement('input[name=image2]')
        ->seeElement('input[name=image3]')
        ->seeElement('input[name=image4]')
        ->seeElement('input[name=image5]')
        ->seeElement('input[name=image6]')
        ->seeInElement('button[type=reset]', 'Reset')
        ->seeInElement('button[type=submit]', 'Submit');

    $this->attach(__DIR__.'/assets/test.jpg', 'image3')
        ->attach(__DIR__.'/assets/test.jpg', 'image4')
        ->attach(__DIR__.'/assets/test.jpg', 'image5')
        ->press('Submit');

    $new = Image::first();

    expect($new->id)->toEqual($old->id);
    expect($new->image1)->toEqual($old->image1);
    expect($new->image2)->toEqual($old->image2);
    expect($new->image6)->toEqual($old->image6);

    $this->assertNotEquals($old->image3, $new->image3);
    $this->assertNotEquals($old->image4, $new->image4);
    $this->assertNotEquals($old->image5, $new->image5);

    File::cleanDirectory(public_path('uploads/images'));
});
test('delete images', function () {
    File::cleanDirectory(public_path('uploads/images'));

    uploadImages();

    $this->visit('admin/images')
        ->seeInElement('td', 1);

    $images = Image::first()->toArray();

    $this->delete('admin/images/1')
        ->dontSeeInDatabase('test_images', ['id' => 1]);

    foreach (range(1, 6) as $index) {
        $this->assertFileDoesNotExist(public_path('uploads/'.$images['image'.$index]));
    }

    $this->visit('admin/images')
        ->seeInElement('td', 'svg');
});
test('batch delete', function () {
    File::cleanDirectory(public_path('uploads/images'));

    uploadImages();
    uploadImages();
    uploadImages();

    $this->visit('admin/images')
        ->seeInElement('td', 1)
        ->seeInElement('td', 2)
        ->seeInElement('td', 3);

    expect(18)->toEqual(fileCountInImageDir());

    expect(3)->toEqual(Image::count());

    $this->delete('admin/images/1,2,3');

    expect(0)->toEqual(Image::count());

    $this->visit('admin/images')
        ->seeInElement('td', 'svg');

    expect(0)->toEqual(fileCountInImageDir());
});
test('upload multiple image', function () {
    File::cleanDirectory(public_path('uploads/images'));

    $this->visit('admin/multiple-images/create')
        ->seeElement('input[type=file][name="pictures[]"][multiple]');

    $path = __DIR__.'/assets/test.jpg';

    $file = new \Illuminate\Http\UploadedFile($path, 'test.jpg', 'image/jpeg', null, true);

    $size = rand(10, 20);
    $files = ['pictures' => array_pad([], $size, $file)];

    $this->call(
        'POST', // $method
        '/admin/multiple-images', // $action
        [], // $parameters
        [],
        $files
    );

    $this->assertResponseStatus(302);
    $this->assertRedirectedTo('/admin/multiple-images');

    expect($size)->toEqual(fileCountInImageDir());

    $pictures = MultipleImage::first()->pictures;

    expect($pictures)->toHaveCount($size);

    foreach ($pictures as $picture) {
        expect(public_path('uploads/'.$picture))->toBeFile();
    }
});
test('remove multiple files', function () {
    File::cleanDirectory(public_path('uploads/images'));

    // upload files
    $path = __DIR__.'/assets/test.jpg';

    $file = new \Illuminate\Http\UploadedFile($path, 'test.jpg', 'image/jpeg', null, true);

    $size = rand(10, 20);
    $files = ['pictures' => array_pad([], $size, $file)];

    $this->call(
        'POST', // $method
        '/admin/multiple-images', // $action
        [], // $parameters
        [],
        $files
    );

    expect($size)->toEqual(fileCountInImageDir());
});
function fileCountInImageDir($dir = 'uploads/images')
{
    $file = new FilesystemIterator(public_path($dir), FilesystemIterator::SKIP_DOTS);

    return iterator_count($file);
}
