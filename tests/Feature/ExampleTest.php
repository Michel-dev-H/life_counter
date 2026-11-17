<?php

use App\NativeComponents\Home;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Native\Mobile\Testing\Native;

uses(RefreshDatabase::class);

test('opens the app entry screen from the root', function () {
    Native::visit('/')
        ->assertScreen(Home::class)
        ->assertSee('DUELO');
});
