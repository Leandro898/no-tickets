<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TicketTypeResource\Pages;
use App\Filament\Resources\TicketTypeResource\RelationManagers;
use App\Models\TicketType;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select; // Necesitamos esto para el selector de eventos
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;

class TicketTypeResource extends Resource
{
    //protected static ?string $model = TicketType::class;

    //protected static ?string $navigationIcon = 'heroicon-o-ticket';
    //protected static ?string $navigationGroup = 'Gestión de Eventos'; // Puedes ajustarlo a un grupo existente o crear uno nuevo

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                // Selector para el evento al que pertenece este tipo de entrada
                // Esto asumirá que tu modelo Evento está en App\Models\Evento
                Select::make('evento_id')
                    ->relationship('evento', 'nombre') // 'evento' es el método de relación en TicketType, 'nombre' es la columna a mostrar
                    ->required()
                    ->searchable()
                    ->preload()
                    ->label('Evento'), // Etiqueta para el campo

                TextInput::make('name')
                    ->required()
                    ->maxLength(255)
                    ->label('Nombre del Tipo de Entrada')
                    ->placeholder('Ej: Entrada General, VIP, Early Bird'),

                TextInput::make('price')
                    ->required()
                    ->numeric()
                    ->step(0.01)
                    ->prefix('ARS$') // Prefijo para moneda
                    ->label('Precio'),

                TextInput::make('available_quantity')
                    ->required()
                    ->numeric()
                    ->minValue(1)
                    ->label('Cantidad Disponible'),

                DateTimePicker::make('start_sale_date')
                    ->label('Inicio de Venta')
                    ->nullable(), // Puede ser nulo si la venta es inmediata

                DateTimePicker::make('end_sale_date')
                    ->label('Fin de Venta')
                    ->nullable(), // Puede ser nulo si la venta no tiene fecha límite

                Toggle::make('is_active')
                    ->label('Activo para Venta')
                    ->default(true),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->label('Tipo de Entrada'),

                TextColumn::make('evento.nombre') // Muestra el nombre del evento relacionado
                    ->label('Evento')
                    ->searchable(),

                TextColumn::make('price')
                    ->money('ARS') // Formatea como moneda argentina
                    ->label('Precio'),

                TextColumn::make('available_quantity')
                    ->label('Stock Disponible'),

                TextColumn::make('sold_quantity')
                    ->label('Vendidas'),

                TextColumn::make('start_sale_date')
                    ->dateTime()
                    ->label('Inicio Venta'),

                TextColumn::make('end_sale_date')
                    ->dateTime()
                    ->label('Fin Venta'),

                ToggleColumn::make('is_active')
                    ->label('Activo'),

                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->label('Creado el'),
            ])
            ->filters([
                // Aquí puedes añadir filtros si es necesario, por ejemplo por evento
                Select::make('evento_id')
                    ->relationship('evento', 'nombre')
                    ->searchable()
                    ->preload()
                    ->label('Filtrar por Evento'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(), // Permite eliminar tipos de entrada
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
            // Podemos añadir Relation Managers aquí más adelante si es necesario,
            // por ejemplo, para ver las PurchasedTickets asociadas.
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTicketTypes::route('/'),
            'create' => Pages\CreateTicketType::route('/create'),
            'edit' => Pages\EditTicketType::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        // Obtener el usuario autenticado (productor)
        $user = auth()->user();

        // Si el usuario tiene un rol que le permite ver todos los eventos (ej. admin), no filtrar
        // (Ajusta 'admin' al nombre de tu rol de administrador si es diferente)
        if ($user && $user->hasRole('admin')) { // Asumiendo que usas Spatie/Laravel-Permission o similar
            return parent::getEloquentQuery();
        }

        // Si es un productor, solo mostrar tipos de entrada de sus eventos
        if ($user) {
            return parent::getEloquentQuery()->whereHas('evento', function (Builder $query) use ($user) {
                $query->where('organizador_id', $user->id);
            });
        }

        // Si no hay usuario autenticado o no es un productor, no mostrar nada
        return parent::getEloquentQuery()->where('id', null); // O abort(403) si prefieres
    }

    // ESTE METODO OCULTA EL TICKET TYPES DEL MENU LATERAL IZQUIERDO
    public static function shouldRegisterNavigation(): bool
    {
        // Retorna false si no quieres que aparezca en el menú.
        // Puedes añadir lógica aquí para mostrarlo solo para ciertos roles, por ejemplo.
        return false;
    }
}