<?php

namespace App\Filament\Resources\VisitorInterests\Schemas;

use App\Models\VisitorInterest;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid as SchemaGrid;
use Filament\Schemas\Components\Section as SchemaSection;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class VisitorInterestForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                SchemaSection::make('Contact')
                    ->schema([
                        SchemaGrid::make(2)
                            ->schema([
                                TextInput::make('name')
                                    ->required()
                                    ->maxLength(120),
                                TextInput::make('email')
                                    ->label('Email')
                                    ->email()
                                    ->maxLength(255)
                                    ->rule('required_without:phone'),
                                TextInput::make('phone')
                                    ->label('Phone')
                                    ->tel()
                                    ->maxLength(25)
                                    ->rule('required_without:email'),
                                TextInput::make('company')
                                    ->label('Company')
                                    ->maxLength(255),
                                TextInput::make('role')
                                    ->label('Role')
                                    ->maxLength(120),
                                TextInput::make('location')
                                    ->label('Location')
                                    ->maxLength(120),
                            ]),
                    ]),
                SchemaSection::make('Interest')
                    ->schema([
                        SchemaGrid::make(2)
                            ->schema([
                                Select::make('interest_type')
                                    ->label('Interest type')
                                    ->options(VisitorInterest::typeOptions())
                                    ->required(),
                                TextInput::make('investment_range')
                                    ->label('Investment range')
                                    ->maxLength(120)
                                    ->visible(fn (Get $get): bool => $get('interest_type') === VisitorInterest::TYPE_INVESTOR),
                                TextInput::make('partnership_area')
                                    ->label('Partnership area')
                                    ->maxLength(120)
                                    ->visible(fn (Get $get): bool => $get('interest_type') === VisitorInterest::TYPE_PARTNERSHIP),
                            ]),
                        Textarea::make('message')
                            ->label('Message')
                            ->required()
                            ->rows(4)
                            ->columnSpanFull(),
                    ]),
                SchemaSection::make('Tracking')
                    ->schema([
                        SchemaGrid::make(2)
                            ->schema([
                                Select::make('status')
                                    ->options(VisitorInterest::statusOptions())
                                    ->default(VisitorInterest::STATUS_NEW)
                                    ->required(),
                                Select::make('source')
                                    ->options(VisitorInterest::sourceOptions())
                                    ->default(VisitorInterest::SOURCE_CALL_CENTER)
                                    ->required(),
                            ]),
                    ])
                    ->collapsible(),
            ]);
    }
}
