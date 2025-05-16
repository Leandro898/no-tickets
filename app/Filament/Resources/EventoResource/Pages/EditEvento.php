<?php

namespace App\Filament\Resources\EventoResource\Pages;

use App\Filament\Resources\EventoResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;

class EditEvento extends EditRecord
{
    protected static string $resource = EventoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('volver')
                ->label('Volver al detalle')
                ->url("/admin/eventos/{$this->record->id}/detalles")
                ->color('gray')
                ->icon('heroicon-o-arrow-left'),

            DeleteAction::make(),
        ];
    }
    
}
