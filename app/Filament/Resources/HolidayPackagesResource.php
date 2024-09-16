<?php

namespace App\Filament\Resources;

use App\Filament\Resources\HolidayPackagesResource\Pages;
use App\Filament\Resources\HolidayPackagesResource\RelationManagers;
use App\Models\HolidayPackages;
use Filament\Forms;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Form;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Str;

class HolidayPackagesResource extends Resource
{
    protected static ?string $model = HolidayPackages::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('category_id')
                    ->relationship('category', 'name')
                    ->required(),
                Forms\Components\TextInput::make('slug')
                    ->required()
                    ->maxLength(255)
                    ->readOnly(),
                Forms\Components\TextInput::make('destinations_name')
                    ->required()
                    ->afterStateUpdated(fn(Set $set, ?string $state)=>$set('slug', Str::slug($state)))
                    ->live(debounce:250)
                    ->maxLength(255),
                Forms\Components\TextInput::make('destinations_location')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Textarea::make('destinations_itenary')
                    ->required(),
                Forms\Components\Textarea::make('about')
                    ->required(),
                Forms\Components\TextInput::make('contact')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('price_per_trip')
                    ->required()
                    ->numeric()
                    ->default(0),
                Forms\Components\Checkbox::make('hotel')
                    ->default(0),
                Forms\Components\Checkbox::make('travel')
                    ->default(0),
                Forms\Components\Checkbox::make('plane')
                    ->default(0),
                FileUpload::make('images')
                    ->required()
                    ->directory('holiday_preview')
                    ->image()
                    ->openable()
                    ->multiple()
                    ->reorderable()
                    ->appendFiles()
                    ->columnSpanFull(),
               
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('destinations_name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('category.name')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('price_per_trip')
                    ->numeric()
                    ->money('USD')
                    ->sortable(),
                Tables\Columns\IconColumn::make('hotel')
                    ->boolean(),
                Tables\Columns\IconColumn::make('travel')
                    ->boolean(),
                Tables\Columns\IconColumn::make('plane')
                    ->boolean(),
                Tables\Columns\TextColumn::make('deleted_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
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
            'index' => Pages\ListHolidayPackages::route('/'),
            'create' => Pages\CreateHolidayPackages::route('/create'),
            'edit' => Pages\EditHolidayPackages::route('/{record}/edit'),
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
