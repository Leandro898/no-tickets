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

class EventoResource extends Resource
{
    protected static ?string $model = Evento::class;
    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';
    protected static ?string $navigationLabel = 'Eventos';
    protected static ?string $pluralModelLabel = 'Eventos';

    public static function form(Form $form): Form
    {
        return $form->schema([
            TextInput::make('nombre')->required(),
            TextInput::make('ubicacion')->required(),
            DateTimePicker::make('fecha_inicio')->required()
                ->seconds(false),
            DateTimePicker::make('fecha_fin')->required()
                ->seconds(false),
            Textarea::make('descripcion'),
            FileUpload::make('imagen')->image()->directory('eventos'),
            Select::make('estado')
                ->options([
                    'activo' => 'Activo',
                    'cancelado' => 'Cancelado',
                    'finalizado' => 'Finalizado',
                ])->default('activo')->required(),
            Hidden::make('organizador_id') // Campo oculto para el organizador
                ->default(fn () => auth()->id())
                ->required()
                ->dehydrated(true),    
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nombre')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('ubicacion'),
                TextColumn::make('fecha_inicio')
                    ->dateTime(),
                TextColumn::make('estado')
                    ->label('Estado')
                    ->badge()
                    ->colors([
                        'success' => fn ($state) => $state === 'activo',
                        'danger' => fn ($state) => $state === 'inactivo',
                        'warning' => fn ($state) => $state === 'finalizado',
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

                // Puedes añadir un filtro por organizador si lo necesitas:
                // SelectFilter::make('organizador')
                //     ->relationship('organizador', 'name')
                //     ->label('Filtrar por Organizador'),
                ])
                ->headerActions([
                    CreateAction::make()
                    ->label('Crear Evento'),                     
            ])
            ->recordUrl(fn (Evento $record) => EventoResource::getUrl('detalles', ['record' => $record->id]));
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
}

