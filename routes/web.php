<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    // Redirect langsung ke halaman login admin sebagai default
    return redirect('/admin');
});
