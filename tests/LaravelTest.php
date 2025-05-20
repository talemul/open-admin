<?php

uses(\TestCase::class);
test('laravel', function () {
    $this->visit('/')
        ->assertResponseStatus(200)
        ->see('Laravel');
});
