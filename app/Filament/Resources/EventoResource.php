<?php

namespace App\Filament\Resources;

use App\Models\Evento;
use App\Filament\Resources\EntradaResource;
use App\Filament\Resources\EventoResource\Pages\ListEventos;
use App\Filament\Resources\EventoResource\Pages\CreateEvento;
use App\Filament\Resources\EventoResource\Pages\EditEvento;
use App\Filament\Resources\EventoResource\Pages\GestionarEntradas;
use App\Filament\Resources\EventoResource\Pages\ReportesEvento;
use App\Filament\Resources\EventoResource\Pages\EventoDetalles;
use App\Filament\Resources\EventoResource\Pages\ListaDigital;
use App\Filament\Resources\EventoResource\Pages\ConfigureSeats;

use Filament\Resources\Resource;
use Filament\Forms\Form;
use Filament\Forms;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Toggle;

use Filament\Tables\Table;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\CreateAction;

use Illuminate\Database\Eloquent\Builder;

class EventoResource extends Resource
{
    protected static ?string $model = Evento::class;
    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';
    protected static ?string $navigationLabel = 'Eventos';
    protected static ?string $pluralModelLabel = 'Eventos';
    protected static ?string $navigationGroup = null; // **NULL para que no esté en grupo**
    protected static ?int $navigationSort = 1;
    // ← aquí le dices “usa slug como clave primaria para las rutas”
    protected static ?string $recordRouteKeyName = 'slug';


    public static function form(Form $form): Form
    {
        return $form->schema([
            Hidden::make('has_seats')
                ->default(fn() => request()->boolean('has_seats'))
                ->dehydrated(true),
            Section::make('Datos principales')
                ->description('Completa la información general del evento.')
                ->schema([
                    TextInput::make('nombre')
                        ->label('Título del Evento')
                        ->required()
                        ->extraAttributes(['class' => 'input-brand'])
                        ->validationAttribute('Título del evento')
                        ->validationMessages([
                            'required' => 'Por favor, ingresa el :attribute.',
                            'unique'   => 'El :attribute ya está registrado.',
                        ]),

                    Textarea::make('ubicacion')
                        ->label('Ubicación')
                        ->rows(4)
                        ->placeholder('Agrega aquí la ubicación del evento. Ejemplo: nombre del lugar, dirección, ciudad, etc.')
                        ->columnSpanFull()
                        ->required()
                        ->extraAttributes(['class' => 'input-brand'])
                        ->validationMessages([
                            'required' => 'Por favor, ingresa la :attribute.',
                            'unique'   => 'El :attribute ya está registrado.',
                        ]),

                    Grid::make(2)->schema([
                        DateTimePicker::make('fecha_inicio')
                            ->label('Fecha y Hora de Inicio')
                            ->required()
                            ->seconds(false)
                            ->extraAttributes(['class' => 'input-brand'])
                            ->validationMessages([
                                'required' => 'Por favor, ingresa la :attribute.',
                            ]),

                        DateTimePicker::make('fecha_fin')
                            ->required()
                            ->seconds(false)
                            ->label('Fecha y Hora de Fin')
                            ->extraAttributes(['class' => 'input-brand'])
                            ->validationMessages([
                                'required' => 'Por favor, ingresa la :attribute.',
                            ]),
                    ])->columns(2),
                ])
                ->columnSpanFull()
                ->icon('heroicon-o-information-circle')
                ->collapsible(),
                //->collapsed()

            // Section::make('Restricciones y requisitos')
            //     ->description('Configura los requisitos para los asistentes.')
            //     ->schema([
            //         // 1) Toggle para habilitar edad mínima
            //         Toggle::make('restringir_edad')
            //             ->label('¿Restringir por edad mínima?')
            //             ->reactive(),  // importante para que los campos dependientes reaccionen

            //         // 2) Campos numéricos, sólo visibles si el toggle está activo
            //         Grid::make(2)
            //             ->schema([
            //                 TextInput::make('edad_min_hombres')
            //                     ->label('Edad mínima Hombres')
            //                     ->numeric()
            //                     ->minValue(0)
            //                     ->default(18)
            //                     ->visible(fn(callable $get) => $get('restringir_edad')),

            //                 TextInput::make('edad_min_mujeres')
            //                     ->label('Edad mínima Mujeres')
            //                     ->numeric()
            //                     ->minValue(0)
            //                     ->default(18)
            //                     ->visible(fn(callable $get) => $get('restringir_edad')),
            //             ])
            //             ->columns(2)
            //             ->columnSpanFull(),

            //         // 3) Toggle para requerir DNI
            //         Toggle::make('requerir_dni')
            //             ->label('¿Requerir DNI para la compra?')
            //             ->helperText('Tus asistentes deberán ingresar su DNI al comprar.')
            //             ->inline(false),
            //     ])
            //     ->icon('heroicon-o-identification')
            //     ->collapsible()
            //     ->collapsed(),

            Section::make('Presentación')
                ->description('Una buena descripción y banner aumentan la conversión.')
                ->schema([
                    Textarea::make('descripcion')
                        ->label('Descripción')
                        ->rows(4)
                        ->placeholder('Agrega aquí una descripción detallada del evento. Ejemplo: temática, artistas, detalles importantes, etc.')
                        ->columnSpanFull()
                        ->required()
                        ->extraAttributes(['class' => 'input-brand'])
                        ->validationMessages([
                            'required' => 'Por favor, ingresa la :attribute.',
                            'unique'   => 'El :attribute ya está registrado.',
                        ]),

                    FileUpload::make('imagen')
                        ->label('Banner (imagen) del evento')
                        ->placeholder('Arrastra y suelta el banner o haz clic para buscarlo')
                        ->image()
                        ->acceptedFileTypes(['image/jpeg', 'image/png'])
                        ->maxSize(2048)
                        ->preserveFilenames()
                        ->directory('eventos')
                        ->disk('public')
                        ->visibility('public')
                        ->enableOpen()
                        ->imagePreviewHeight(200)
                        ->helperText('Subí un banner vistoso y de buena calidad (JPG o PNG, máx. 2MB).')
                        ->columnSpanFull(),
                ])
                ->icon('heroicon-o-photo')
                ->collapsible()
                ->collapsed(),

            Hidden::make('estado')
                ->default(fn() => 'activo')
                ->dehydrated(true),

            Hidden::make('organizador_id')
                ->default(fn() => auth()->id())
                ->required()
                ->dehydrated(true),
        ]);
    }



    public static function table(Table $table): Table
    {
        return $table
            ->emptyStateHeading('No hay eventos')
            ->emptyStateDescription('Crea un evento para comenzar.')
            ->columns([
                TextColumn::make('nombre')
                    ->searchable() // Permite buscar por el nombre del evento
                    ->sortable(),
                TextColumn::make('ubicacion'),
                TextColumn::make('fecha_inicio')
                    ->dateTime(),
                TextColumn::make('estado')
                    ->label('Estado')
                    ->badge()
                    ->colors([
                        'success' => fn($state) => $state === 'activo',
                        'danger' => fn($state) => $state === 'inactivo',
                        'warning' => fn($state) => $state === 'finalizado',
                    ]),
            ])
            ->filters([
                // Aquí puedes añadir filtros adicionales, por ejemplo, por estado:
                SelectFilter::make('estado')
                    ->options([
                        'activo' => 'Activo',
                        'cancelado' => 'Cancelado',
                        'finalizado' => 'Finalizado',
                    ])
                    ->label('Filtrar por Estado'),

                // // Puedes añadir un filtro por organizador si lo necesitas:
                // // SelectFilter::make('organizador')
                // //     ->relationship('organizador', 'name')
                // //     ->label('Filtrar por Organizador'),
            ]);


        // ->recordUrl(fn(Evento $record) => EventoResource::getUrl('detalles', ['record' => $record->id]))
    }

    public static function getPages(): array
    {
        return [
            'index'              => ListEventos::route('/'),
            'create'             => CreateEvento::route('/create'),
            'edit'               => EditEvento::route('/{record}/edit'),
            'gestionar-entradas' => GestionarEntradas::route('/{record}/gestionar-entradas'),
            'reportes'           => ReportesEvento::route('/{record}/reportes'),
            'detalles'           => EventoDetalles::route('/{record}/detalles'),
            'lista-digital'      => ListaDigital::route('/{record}/lista-digital'),
            'configure-seats' => ConfigureSeats::route('/{record}/asientos'),
        ];
    }


    public static function getRelations(): array
    {
        return [
            //EntradasRelationManager::class, // Esta linea me activa la vista de la lista de entradas que tiene el evento. Por las dudas que quiera habilitarlo en el futuro
        ];
    }

    public static function getRecordUrl($record, string $pageName = 'detalles'): string
    {
        // ahora $record ya resolverá al slug
        return static::getUrl($pageName, ['record' => $record]);
    }

    public static function getModel(): string
    {
        return \App\Models\Evento::class;
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('organizador_id', auth()->id());
    }

}
