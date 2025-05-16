<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EntradaResource\Pages;
use App\Filament\Resources\EntradaResource\RelationManagers;
use App\Models\Entrada;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Tables\Columns\TextColumn;

class EntradaResource extends Resource
{
    protected static ?string $model = Entrada::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    // Desactivar navegacion lateral del recurso Entradas
    protected static bool $shouldRegisterNavigation = false;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('nombre')
                    ->required()
                    ->maxLength(255),

                Forms\Components\Textarea::make('descripcion')
                    ->rows(3)
                    ->maxLength(500)
                    ->label('Descripcion'),

                Forms\Components\TextInput::make('stock_inicial')
                    ->numeric()
                    ->required()
                    ->label('Stock inicial'),

                Forms\Components\TextInput::make('max_por_compra')
                    ->numeric()
                    ->required()
                    ->label('Máximo por compra'),

                Forms\Components\TextInput::make('precio')
                    ->numeric()
                    ->required()
                    ->label('Precio'),

                Forms\Components\Checkbox::make('valido_todo_el_evento')
                    ->label('Este producto es válido para cualquier día del evento'),

                // Agregá aquí los demás campos que necesites

                Forms\Components\Hidden::make('evento_id')
                    ->default(fn () => request()->get('evento_id'))
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nombre')->label('Nombre'),
                TextColumn::make('precio')->label('Precio'),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
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
            'index' => Pages\ListEntradas::route('/'),
            'create' => Pages\CreateEntrada::route('/create'),
            'edit' => Pages\EditEntrada::route('/{record}/edit'),
        ];
    }

    protected function getTableQuery()
    {
        $query = parent::getTableQuery();

        if ($eventoId = request()->get('evento_id')) {
            $query->where('evento_id', $eventoId);
        }

        return $query;
    }

}
