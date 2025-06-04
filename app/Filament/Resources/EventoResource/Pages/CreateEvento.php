<?php

namespace App\Filament\Resources\EventoResource\Pages;

use App\Filament\Resources\EventoResource;
use Filament\Resources\Pages\CreateRecord;
use App\Filament\Resources\EntradaResource;

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
}
