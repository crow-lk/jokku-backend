<?php

namespace App\Filament\Resources\VisitorInterests;

use App\Filament\Resources\VisitorInterests\Pages\CreateVisitorInterest;
use App\Filament\Resources\VisitorInterests\Pages\EditVisitorInterest;
use App\Filament\Resources\VisitorInterests\Pages\ListVisitorInterests;
use App\Filament\Resources\VisitorInterests\Schemas\VisitorInterestForm;
use App\Filament\Resources\VisitorInterests\Tables\VisitorInterestsTable;
use App\Models\VisitorInterest;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class VisitorInterestResource extends Resource
{
    protected static ?string $model = VisitorInterest::class;

    protected static ?string $navigationLabel = 'Visitor Interests';

    protected static ?string $modelLabel = 'Visitor Interest';

    protected static ?string $pluralModelLabel = 'Visitor Interests';

    protected static ?string $recordTitleAttribute = 'name';

    protected static string|UnitEnum|null $navigationGroup = 'Marketing';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedSparkles;

    public static function form(Schema $schema): Schema
    {
        return VisitorInterestForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return VisitorInterestsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListVisitorInterests::route('/'),
            'create' => CreateVisitorInterest::route('/create'),
            'edit' => EditVisitorInterest::route('/{record}/edit'),
        ];
    }
}
