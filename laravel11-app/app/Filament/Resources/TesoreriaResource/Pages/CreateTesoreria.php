<?php

namespace App\Filament\Resources\TesoreriaResource\Pages;

use App\Filament\Resources\TesoreriaResource;
use Carbon\Carbon;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreateTesoreria extends CreateRecord
{
    protected static string $resource = TesoreriaResource::class;


}
