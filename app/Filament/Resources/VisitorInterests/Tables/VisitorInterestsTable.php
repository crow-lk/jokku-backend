<?php

namespace App\Filament\Resources\VisitorInterests\Tables;

use App\Models\VisitorInterest;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class VisitorInterestsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('interest_type')
                    ->label('Type')
                    ->badge()
                    ->color(fn (?string $state): string => match ($state) {
                        VisitorInterest::TYPE_INVESTOR => 'success',
                        VisitorInterest::TYPE_PARTNERSHIP => 'info',
                        default => 'warning',
                    })
                    ->formatStateUsing(fn (?string $state): string => VisitorInterest::typeOptions()[$state] ?? 'Unknown')
                    ->sortable(),
                TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('phone')
                    ->label('Phone')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('company')
                    ->label('Company')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (?string $state): string => match ($state) {
                        VisitorInterest::STATUS_CONTACTED => 'info',
                        VisitorInterest::STATUS_QUALIFIED => 'success',
                        VisitorInterest::STATUS_ARCHIVED => 'gray',
                        default => 'warning',
                    })
                    ->formatStateUsing(fn (?string $state): string => VisitorInterest::statusOptions()[$state] ?? 'Unknown')
                    ->sortable(),
                TextColumn::make('source')
                    ->badge()
                    ->color(fn (?string $state): string => match ($state) {
                        VisitorInterest::SOURCE_CALL_CENTER => 'info',
                        VisitorInterest::SOURCE_ADMIN => 'primary',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (?string $state): string => VisitorInterest::sourceOptions()[$state] ?? 'Unknown')
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('Received')
                    ->since()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('interest_type')
                    ->label('Type')
                    ->options(VisitorInterest::typeOptions())
                    ->multiple(),
                SelectFilter::make('status')
                    ->options(VisitorInterest::statusOptions())
                    ->multiple(),
                SelectFilter::make('source')
                    ->options(VisitorInterest::sourceOptions())
                    ->multiple(),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
