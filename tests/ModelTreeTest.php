<?php

uses(\TestCase::class);
use Tests\Models\Tree;
beforeEach(function () {
});
test('select options', function () {
    $rootText = 'Root Text';

    $options = Tree::selectOptions(function ($query) {
        return $query->where('uri', '');
    }, $rootText);

    $count = Tree::query()->where('uri', '')->count();

    expect($rootText)->toEqual(array_shift($options));
    expect($count)->toEqual(count($options));
});
