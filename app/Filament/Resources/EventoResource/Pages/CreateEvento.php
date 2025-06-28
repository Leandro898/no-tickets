<?php

namespace App\Filament\Resources\EventoResource\Pages;

use App\Filament\Resources\EventoResource;
use Filament\Resources\Pages\CreateRecord;
use App\Filament\Resources\EntradaResource;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Forms\Actions\CancelAction;  // <--  también de Forms



class CreateEvento extends CreateRecord
{
    protected static string $resource = EventoResource::class;

    protected function getRedirectUrl(): string
    {
        return EntradaResource::getUrl('create', ['evento_id' => $this->record->id]);
    }

    public function getBreadcrumbs(): array
    {
        return [];
    }

    public function getTitle(): string
    {
        return 'Crear Evento';
    }

    public static function canCreateAnother(): bool
    {
        return false;
    }
    
}
