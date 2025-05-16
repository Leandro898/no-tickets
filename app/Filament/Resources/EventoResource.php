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
            DateTimePicker::make('fecha_inicio')->required(),
            DateTimePicker::make('fecha_fin')->required(),
            Textarea::make('descripcion'),
            FileUpload::make('imagen')->image()->directory('eventos'),
            Select::make('estado')
                ->options([
                    'activo' => 'Activo',
                    'cancelado' => 'Cancelado',
                    'finalizado' => 'Finalizado',
                ])->default('activo')->required(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('nombre')->searchable()->sortable(),
            TextColumn::make('ubicacion'),
            TextColumn::make('fecha_inicio')->dateTime(),
            TextColumn::make('estado')
                ->label('Estado')
                ->badge()
                ->colors([
                    'success' => fn ($state) => $state === 'activo',
                    'danger' => fn ($state) => $state === 'inactivo',
                ])
        ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListEventos::route('/'),
            'create' => Pages\CreateEvento::route('/create'),
            'edit' => Pages\EditEvento::route('/{record}/edit'),
            'detalles' => Pages\EventoDetalles::route('/{record}/detalles'),
        ];
    }

    public static function getRelations(): array
    {
        return [
            EntradasRelationManager::class,
        ];
    }

    public static function getRecordUrl($record, string $pageName = 'view'): string
    {
        return static::getUrl('detalles', ['record' => $record]);
    }
}

