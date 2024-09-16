<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TransactionsResource\Pages;
use App\Filament\Resources\TransactionsResource\RelationManagers;
use App\Models\HolidayPackages;
use App\Models\Transactions;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Set;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Actions\Action;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class TransactionsResource extends Resource
{
    protected static ?string $model = Transactions::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('user_id')
                    ->relationship('customer','name')
                    ->required(),
                Forms\Components\Select::make('package_id')
                    ->relationship('package', 'destinations_name')
                    ->required()
                    ->live(debounce: 250)
                    ->afterStateUpdated(function ($state, $set) {
                        if ($state) {
                            // Fetch the package from the database
                            $package = HolidayPackages::find($state);
                
                            if ($package) {
                                // Update the price_per_trip field with the fetched value
                                $pricePerTrip = $package->price_per_trip;
                                $fee = $pricePerTrip * 0.11;
                                
                                $set('price_per_trip', $pricePerTrip);
                                $set('fee', $fee);
                                $set('total_price', $pricePerTrip + $fee);
                            }
                        } else {
                            // Clear the fields if no package is selected
                            $set('price_per_trip', 0);
                            $set('fee', 0);
                            $set('total_price', 0);
                        }
                    }),
                    Forms\Components\DatePicker::make('start_date')
                    ->required()
                    ->live(debounce: 250)
                    ->afterStateUpdated(function ($state, Set $set, $get) {
                        $endDate = $get('end_date');
                        if ($endDate) {
                            // Calculate the total days if end date is selected
                            $startDate = \Carbon\Carbon::parse($state);
                            $endDate = \Carbon\Carbon::parse($endDate);
                            $totalDays = $startDate->diffInDays($endDate) + 1; // +1 to include both start and end day
                            $set('total_days', $totalDays);
                        }
                    }),
                
                Forms\Components\DatePicker::make('end_date')
                    ->required()
                    ->live(debounce: 250)
                    ->afterStateUpdated(function ($state, Set $set, $get) {
                        $startDate = $get('start_date');
                        if ($startDate) {
                            // Calculate the total days if start date is selected
                            $startDate = \Carbon\Carbon::parse($startDate);
                            $endDate = \Carbon\Carbon::parse($state);
                            $totalDays = $startDate->diffInDays($endDate) + 1; // +1 to include both start and end day
                            $set('total_days', $totalDays);
                        }
                    }),
                Forms\Components\TextInput::make('total_days')
                    ->required()
                    ->numeric()
                    ->default(0)
                    ->readOnly(),
                Forms\Components\TextInput::make('price_per_trip')
                    ->required()
                    ->numeric()
                    ->default(0)
                    // ->afterStateUpdated(function (Set $set, ?float $state) {
                    //     $fee = $state * 0.11;
                    //     $set('fee', $fee);
                    //     $set('total_price', $state + $fee);
                    // })
                    // ->live(debounce: 250),
                    ->readOnly(),
                Forms\Components\TextInput::make('fee')
                    ->required()
                    ->numeric()
                    ->default(0)
                    ->readOnly(),
                    
                Forms\Components\TextInput::make('total_price')
                    ->required()
                    ->numeric()
                    ->default(0)
                    ->readOnly(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('customer.name')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('package.destinations_name')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('start_date')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('end_date')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('total_days')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('total_price')
                    ->numeric()
                    ->money('USD')
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                ->badge()
                ->color(fn(string $state): string => match($state){
                    'waiting' =>'gray',
                    'approved' =>'info',
                    'canceled' =>'danger',

                }),
               
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                
                Action::make('approve')
                ->button()
                ->color('success')
                ->requiresConfirmation()
                ->action(function(Transactions $transaction){
                    Transactions::find($transaction->id)->update([
                        'status' =>'approved'
                    ]);
                    Notification::make()->success()->title('Transaction Approved')->body('Transaction has been approved successfully')->icon('heroicon-o-check')->send();
                })
                ->hidden(fn(Transactions $transaction)=>$transaction->status !== 'waiting'),

                Action::make('Reject')
                ->button()
                ->color('danger')
                ->requiresConfirmation()
                ->action(function(Transactions $transaction){
                    Transactions::find($transaction->id)->update([
                        'status' => 'canceled'
                    ]);
                    Notification::make()->success()->title('Transaction Canceled')->body('Transaction has been canceled successfully')->icon('heroicon-o-x')->send();
                })
                ->hidden(fn(Transactions $transaction) => $transaction->status !== 'waiting'),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                ]),
            ]);
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
            'index' => Pages\ListTransactions::route('/'),
            'create' => Pages\CreateTransactions::route('/create'),
            'edit' => Pages\EditTransactions::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
