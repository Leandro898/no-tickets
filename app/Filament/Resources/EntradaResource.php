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
use Filament\Forms\Components\TextInput; // Para campos de texto
use Filament\Forms\Components\Textarea; // Para descripciones multilínea
use Filament\Forms\Components\DateTimePicker; // Para campos de fecha y hora
use Filament\Forms\Components\Checkbox; // Ya lo tienes, pero para recordar
use Filament\Forms\Components\Hidden; // Ya lo tienes, pero para recordar
use Filament\Forms\Components\Select; // Si en algún momento necesitas un selector de eventos en el form principal
use Filament\Resources\Pages\CreateRecord;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Log;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Card;



class EntradaResource extends Resource
{
    protected static ?string $model = Entrada::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    // Desactivar navegacion lateral del recurso Entradas
    protected static bool $shouldRegisterNavigation = false;
    // no se que es esta linea
    //protected static ?string $navigationGroup = 'Gestión de Eventos'; // Si usas grupos, lo agregamos aquí.

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Hidden::make('evento_id')
                    ->default(fn() => request()->get('evento_id'))
                    ->required(),

                TextInput::make('nombre')
                    ->label('Nombre de la Entrada')
                    ->placeholder('Ej: Entrada General, VIP, Early Bird')
                    ->required()
                    ->columnSpanFull(),

                Grid::make(2)
                    ->schema([
                        TextInput::make('stock_inicial')
                            ->label('Stock Inicial')
                            ->numeric()
                            ->minValue(0)
                            ->required()
                            // siempre visible, pero no editable al editar
                            ->disabledOn(EditRecord::class)
                            ->columnSpan(2),

                        TextInput::make('agregar_stock')
                            ->label('Agregar más entradas')
                            ->numeric()
                            ->minValue(0)
                            ->default(0)
                            ->helperText('Cantidad a sumar al stock actual')
                            // solo en editar
                            ->visibleOn(EditRecord::class)
                            ->hiddenOn(CreateRecord::class)
                            ->columnSpan(2),
                    ]),

                Grid::make(2)
                    ->schema([
                        TextInput::make('precio')
                            ->label('Precio')
                            ->prefix('ARS$')
                            ->numeric()
                            ->step(0.01)
                            ->required(),

                        Textarea::make('descripcion')
                            ->label('Descripción')
                            ->rows(3)
                            ->maxLength(200),
                    ]),

                TextInput::make('max_por_compra')
                    ->label('Máximo de entradas por Compra')
                    ->numeric()
                    ->minValue(1)
                    ->nullable()
                    ->helperText('Dejar vacío para ilimitado')
                    ->columnSpanFull(),
            ]);
    }


    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nombre')->label('Nombre'),
                TextColumn::make('precio')->label('Precio')->money('ARS'),

                // --- Columnas de STOCK en la tabla ---

                TextColumn::make('stock_inicial')->label('Stock Inicial'),
                TextColumn::make('stock_actual')->label('Stock Actual'),
                TextColumn::make('max_por_compra')->label('Máx. x Compra'),

                // --- Columnas de FECHA DE DISPONIBILIDAD ---

                TextColumn::make('disponible_desde')->dateTime()->label('Desde'),
                TextColumn::make('disponible_hasta')->dateTime()->label('Hasta'),
                // --------------------------------------------

                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true) // Oculto por defecto
                    ->label('Creado el'),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
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
            // Aquí podrás añadir Relation Managers, por ejemplo, para ver las PurchasedTickets asociadas a esta Entrada
            // RelationManagers\PurchasedTicketsRelationManager::class, // Lo veremos en una fase posterior
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListEntradas::route('/'),
            'create' => Pages\CreateEntrada::route('/create'),
            'edit' => Pages\EditEntrada::route('/{record}/edit'),
            'manage-entradas' => Pages\ManageEntradas::route('/manage-entradas'),
        ];
    }

    protected function getTableQuery()
    {
        $query = parent::getTableQuery();

        if ($eventoId = request()->get('evento_id')) {
            $query->where('evento_id', $eventoId);
        }

        // --- AÑADIMOS LA LÓGICA DE RESTRICCIÓN DE ACCESO (solo si aún no la tenías aquí) ---
        $user = auth()->user();
        if ($user && $user->hasRole('admin')) { // Asumiendo que usas Spatie/Laravel-Permission o similar
            return $query; // Admins ven todo
        }

        if ($user) {
            return $query->whereHas('evento', function (Builder $eventoQuery) use ($user) {
                $eventoQuery->where('organizador_id', $user->id);
            });
        }

        // Si no hay usuario o no es productor, no mostrar nada
        return $query->where('id', null);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->whereHas('evento', function ($query) {
                $query->where('organizador_id', auth()->id());
            });
    }

    /** Esto inicializa el stock_actual con el valor que coloca el productor en stock_inicial */
    public static function mutateFormDataBeforeCreate(array $data): array
    {
        // validación de organizador…
        $data['stock_actual'] = $data['stock_inicial'] ?? 0;
        return $data;
    }


    // YA SE EJECUTA EN EditEntrada.php
    // public static function mutateFormDataBeforeSave(array $data): array
    // {
    //     // Validación de organizador
    //     $evento = \App\Models\Evento::find($data['evento_id'] ?? null);
    //     if (!$evento || $evento->organizador_id !== auth()->id()) {
    //         abort(403, 'No estás autorizado para modificar entradas en este evento.');
    //     }

    //     // Opcional: log para debug
    //     Log::info('mutateFormDataBeforeSave recibió:', $data);

    //     // Suma el stock adicional al stock_actual existente
    //     $entrada = Entrada::findOrFail(request()->route('record'));
    //     $adicional = intval(request()->input('agregar_stock', 0));
    //     $data['stock_actual'] = $entrada->stock_actual + $adicional;

    //     return $data;
    // }
}
