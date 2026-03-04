<?php

namespace App\Filament\Resources\VisitorInterests\Pages;

use App\Filament\Resources\VisitorInterests\VisitorInterestResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListVisitorInterests extends ListRecords
{
    protected static string $resource = VisitorInterestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
