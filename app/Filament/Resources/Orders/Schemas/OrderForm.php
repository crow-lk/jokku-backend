<?php

namespace App\Filament\Resources\Orders\Schemas;

use App\Filament\Actions\SendOrderSmsAction;
use App\Filament\Resources\Orders\OrderResource;
use App\Models\Order;
use App\Models\User;
use Filament\Forms;
use Filament\Schemas\Components\Actions as ActionsComponent;
use Filament\Schemas\Components\Grid as SchemaGrid;
use Filament\Schemas\Components\Section as SchemaSection;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;

class OrderForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                SchemaSection::make('Statuses & fulfillment')
                    ->schema([
                        Forms\Components\Select::make('status')
                            ->label('Order status')
                            ->options(OrderResource::statusOptions())
                            ->required()
                            ->native(false),
                        Forms\Components\Select::make('payment_status')
                            ->label('Payment status')
                            ->options(OrderResource::paymentStatusOptions())
                            ->required()
                            ->native(false),
                        Forms\Components\Select::make('fulfillment_status')
                            ->label('Fulfillment status')
                            ->options(OrderResource::fulfillmentStatusOptions())
                            ->required()
                            ->native(false),
                        Forms\Components\Select::make('payment_method_id')
                            ->label('Payment Method')
                            ->relationship('paymentMethod', 'name')
                            ->searchable()
                            ->required()
                            ->native(false),
                    ])
                    ->columns(4)
                    ->columnSpanFull(),
                SchemaSection::make('Customer contact')
                    ->schema([
                        Forms\Components\Select::make('user_id')
                            ->label('Customer account')
                            ->relationship('user', 'name')
                            ->getOptionLabelFromRecordUsing(function (User $record): string {
                                $label = $record->name;

                                if (filled($record->email)) {
                                    $label .= ' - '.$record->email;
                                }

                                if (filled($record->mobile)) {
                                    $label .= ' - '.$record->mobile;
                                }

                                return $label;
                            })
                            ->searchable()
                            ->preload()
                            ->native(false)
                            ->live()
                            ->afterStateUpdated(function (Get $get, Set $set, ?string $state): void {
                                if (blank($state)) {
                                    return;
                                }

                                $user = User::query()->find($state);

                                if (! $user) {
                                    return;
                                }

                                if (blank($get('customer_name'))) {
                                    $set('customer_name', $user->name);
                                }

                                if (blank($get('customer_email'))) {
                                    $set('customer_email', $user->email);
                                }

                                if (blank($get('customer_phone'))) {
                                    $set('customer_phone', $user->mobile);
                                }
                            })
                            ->columnSpanFull(),
                        SchemaGrid::make([
                            'default' => 1,
                            'md' => 3,
                        ])
                            ->schema([
                                Forms\Components\TextInput::make('customer_name')
                                    ->label('Customer name')
                                    ->maxLength(255)
                                    ->rule('nullable')
                                    ->dehydrateStateUsing(fn (?string $state): ?string => blank($state) ? null : $state),
                                Forms\Components\TextInput::make('customer_email')
                                    ->label('Email')
                                    ->email()
                                    ->maxLength(255)
                                    ->rule('nullable')
                                    ->dehydrateStateUsing(fn (?string $state): ?string => blank($state) ? null : $state),
                                Forms\Components\TextInput::make('customer_phone')
                                    ->label('Phone')
                                    ->tel()
                                    ->maxLength(50)
                                    ->rule('nullable')
                                    ->dehydrateStateUsing(fn (?string $state): ?string => blank($state) ? null : $state),
                            ]),
                    ])
                    ->columns(1)
                    ->columnSpan(1),
                SchemaSection::make('Shipping details')
                    ->schema([
                        Forms\Components\TextInput::make('shipping_address.line1')
                            ->label('Address Line 1')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('shipping_address.line2')
                            ->label('Address Line 2')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('shipping_address.city')
                            ->label('City')
                            ->maxLength(100),
                        Forms\Components\TextInput::make('shipping_address.state')
                            ->label('State/Province')
                            ->maxLength(100),
                        Forms\Components\TextInput::make('shipping_address.postal_code')
                            ->label('Postal Code')
                            ->maxLength(20),
                        Forms\Components\TextInput::make('shipping_address.country')
                            ->label('Country')
                            ->maxLength(100),
                    ])
                    ->columns(2)
                    ->columnSpan(1),

                SchemaSection::make('Order Items')
                    ->schema([
                        Forms\Components\Repeater::make('items')
                            ->label('Products')
                            ->schema([
                                Forms\Components\Select::make('product_id')
                                    ->label('Product')
                                    ->options(function () {
                                        return \App\Models\Product::pluck('name', 'id')->toArray();
                                    })
                                    ->searchable()
                                    ->required()
                                    ->live()
                                    ->afterStateUpdated(function ($state, callable $set) {
                                        $set('product_variant_id', null);
                                    }),
                                Forms\Components\Select::make('product_variant_id')
                                    ->label('Variant (Size/Color)')
                                    ->options(function (callable $get) {
                                        $productId = $get('product_id');
                                        if (! $productId) {
                                            return [];
                                        }
                                        $variants = \App\Models\ProductVariant::where('product_id', $productId)->get();

                                        return $variants->mapWithKeys(function ($variant) {
                                            $size = $variant->size ? $variant->size->name : '';
                                            $colors = $variant->colors->pluck('name')->implode(', ');
                                            $label = $size;
                                            if ($colors) {
                                                $label .= ($label ? ' / ' : '').$colors;
                                            }

                                            return [$variant->id => $label ?: $variant->sku];
                                        });
                                    })
                                    ->searchable()
                                    ->required()
                                    ->live()
                                    ->afterStateUpdated(function ($state, callable $set, callable $get): void {
                                        if ($state) {
                                            $variant = \App\Models\ProductVariant::find($state);
                                            if ($variant) {
                                                $price = (float) ($variant->selling_price ?? 0);
                                                $set('unit_price', $price);
                                                $qty = (float) $get('quantity');
                                                $set('line_total', $qty * $price);
                                            }
                                        }
                                    }),
                                Forms\Components\TextInput::make('quantity')
                                    ->label('Quantity')
                                    ->numeric()
                                    ->minValue(1)
                                    ->required()
                                    ->live()
                                    ->afterStateUpdated(function ($state, callable $set, callable $get): void {
                                        $qty = (float) ($state ?? 0);
                                        $price = (float) $get('unit_price');
                                        $set('line_total', $qty * $price);
                                    }),
                                Forms\Components\TextInput::make('unit_price')
                                    ->label('Unit Price')
                                    ->numeric()
                                    ->required()
                                    ->live()
                                    ->afterStateUpdated(function ($state, callable $set, callable $get): void {
                                        $qty = (float) $get('quantity');
                                        $price = (float) ($state ?? 0);
                                        $set('line_total', $qty * $price);
                                    }),
                                Forms\Components\TextInput::make('line_total')
                                    ->label('Line Total')
                                    ->numeric()
                                    ->disabled()
                                    ->dehydrated(),
                            ])
                            ->minItems(1)
                            ->columns(5)
                            ->createItemButtonLabel('Add Product'),
                    ])
                    ->columnSpanFull(),
                SchemaSection::make('Notes')
                    ->schema([
                        Forms\Components\Textarea::make('notes')
                            ->label('Customer notes')
                            ->rows(4)
                            ->helperText('Visible to fulfillment and support teams.')
                            ->dehydrateStateUsing(fn (?string $state): ?string => blank($state) ? null : $state),
                    ])
                    ->columns(1)
                    ->columnSpanFull(),
                SchemaSection::make('Notify.lk SMS')
                    ->schema([
                        ActionsComponent::make([
                            SendOrderSmsAction::make(),
                        ])
                            ->fullWidth(),
                    ])
                    ->visible(fn (?Order $record): bool => (bool) $record?->exists)
                    ->columns(1)
                    ->columnSpanFull(),
            ]);
    }
}
