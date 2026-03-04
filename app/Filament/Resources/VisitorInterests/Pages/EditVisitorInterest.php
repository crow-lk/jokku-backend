<?php

namespace App\Filament\Resources\VisitorInterests\Pages;

use App\Filament\Resources\Pages\BaseEditRecord;
use App\Filament\Resources\VisitorInterests\VisitorInterestResource;
use Filament\Actions\DeleteAction;

class EditVisitorInterest extends BaseEditRecord
{
    protected static string $resource = VisitorInterestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
