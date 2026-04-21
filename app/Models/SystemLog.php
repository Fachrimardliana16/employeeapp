<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\File;

class SystemLog extends Model
{
    use \Sushi\Sushi; // I will use Sushi but if not exist I will mock it or just use simple array

    protected $fillable = ['id', 'date', 'level', 'message', 'full_text'];

    /**
     * Since I want to use standard Filament Table, 
     * I will use Sushi if possible, or I will use a manual Array approach.
     * To avoid composer issues, I will Use a manual Livewire Table approach in the Resource.
     */
}
