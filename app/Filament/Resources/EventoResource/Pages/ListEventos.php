<?php

namespace App\Filament\Resources\EventoResource\Pages;

use App\Filament\Resources\EventoResource;
use Filament\Resources\Pages\ListRecords;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Actions\Action;
use Filament\Pages\Actions\CreateAction;
use Closure;

class ListEventos extends ListRecords
{
    protected static string $resource = EventoResource::class;

    public function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query)
            ->columns([
                TextColumn::make('nombre')
                    ->label('Nombre')
                    ->url(null), // ← Desactiva el link en el nombre
            ])
            ->actions([
                Action::make('ver_detalles')
                    ->label('Ver detalles')
                    ->url(fn ($record): string => EventoResource::getUrl('detalles', ['record' => $record->id]))
                    ->icon('heroicon-o-eye'),
            ])
            ->recordUrl(
                fn ($record) => EventoResource::getUrl('detalles', ['record' => $record->id])
            );
    }


    protected function getRecordUrlUsing(): ?Closure
    {
        return fn ($record): string => EventoResource::getUrl('detalles', ['record' => $record->id]);
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
