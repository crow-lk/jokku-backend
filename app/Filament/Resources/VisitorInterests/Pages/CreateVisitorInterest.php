<?php

namespace App\Filament\Resources\VisitorInterests\Pages;

use App\Filament\Resources\Pages\BaseCreateRecord;
use App\Filament\Resources\VisitorInterests\VisitorInterestResource;
use App\Models\VisitorInterest;

class CreateVisitorInterest extends BaseCreateRecord
{
    protected static string $resource = VisitorInterestResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['created_by_user_id'] = auth()->id();
        $data['source'] ??= VisitorInterest::SOURCE_ADMIN;

        return $data;
    }
}
