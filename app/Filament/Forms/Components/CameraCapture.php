<?php

namespace App\Filament\Forms\Components;

use Filament\Forms\Components\Field;

class CameraCapture extends Field
{
    protected string $view = 'filament.forms.components.camera-capture';

    protected function setUp(): void
    {
        parent::setUp();

        $this->dehydrated(true);
    }
}
