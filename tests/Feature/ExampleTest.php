<?php

use App\Livewire\HomeDashboard;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('opens the app dashboard from the root', function () {
    $this->get('/')->assertOk()->assertSeeLivewire(HomeDashboard::class);
});
