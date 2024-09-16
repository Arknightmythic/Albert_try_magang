<?php

namespace App\Filament\Resources\HolidayPackagesResource\Pages;

use App\Filament\Resources\HolidayPackagesResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListHolidayPackages extends ListRecords
{
    protected static string $resource = HolidayPackagesResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
