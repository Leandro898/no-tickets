<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EventoResource\Pages;
use App\Models\Evento;
use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use App\Filament\Resources\EventoResource\RelationManagers\EntradasRelationManager;
use App\Filament\Resources\EventoResource\Pages\ListEventos;
use Filament\Forms\Components\Hidden;
use Filament\Tables\Actions\CreateAction;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Actions\Action;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\EventoResource\Pages\ListaDigital;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Toggle;
use App\Filament\Resources\EntradaResource;


class EventoResource extends Resource
{
    protected static ?string $model = Evento::class;
    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';
    protected static ?string $navigationLabel = 'Eventos';
    protected static ?string $pluralModelLabel = 'Eventos';
    protected static ?string $navigationGroup = null; // **NULL para que no esté en grupo**
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form->schema([
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

            Section::make('Restricciones y requisitos')
                ->description('Configura los requisitos para los asistentes.')
                ->schema([
                    Section::make('Edad mínima')->schema([
                        Toggle::make('enable_min_age')
                            ->label('¿Restringir por edad mínima?')
                            ->reactive()
                            ->inline(false),

                        Grid::make(2)
                            ->schema([
                                TextInput::make('min_age_male')
                                    ->label('Edad mínima Hombres')
                                    ->numeric()
                                    ->default(18)
                                    ->extraAttributes(['class' => 'input-brand']),

                                TextInput::make('min_age_female')
                                    ->label('Edad mínima Mujeres')
                                    ->numeric()
                                    ->default(18)
                                    ->extraAttributes(['class' => 'input-brand']),
                            ])
                            ->columns(2)
                            ->visible(fn(callable $get) => $get('enable_min_age')),
                    ]),

                    Toggle::make('require_dni')
                        ->label('Requerir DNI para la compra')
                        ->helperText('Tus asistentes deberán ingresar su DNI al comprar.')
                        ->inline(false),
                ])
                ->icon('heroicon-o-identification')
                ->collapsible(),

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
                ->collapsible(),

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
            //'index' => ListEventos::class,
            'index' => Pages\ListEventos::route('/'),
            'create' => Pages\CreateEvento::route('/create'),
            'edit' => Pages\EditEvento::route('/{record}/edit'),
            'gestionar-entradas' => Pages\GestionarEntradas::route('/{record}/gestionar-entradas'),
            'reportes' => Pages\ReportesEvento::route('/{record}/reportes'),
            //'view' => Pages\EventoDetalles::route('/{record}'),
            'detalles' => Pages\EventoDetalles::route('/{record}/detalles'),
            'lista-digital' => Pages\ListaDigital::route('/{record}/lista-digital'),
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
        return static::getUrl('detalles', ['record' => $record]);
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
