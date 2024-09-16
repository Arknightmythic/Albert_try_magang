<?php

namespace App\Filament\Resources\HolidayPackagesResource\Pages;

use App\Filament\Resources\HolidayPackagesResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditHolidayPackages extends EditRecord
{
    protected static string $resource = HolidayPackagesResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
            Actions\ForceDeleteAction::make(),
            Actions\RestoreAction::make(),
        ];
    }
}
