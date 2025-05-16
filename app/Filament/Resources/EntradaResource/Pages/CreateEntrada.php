<?php

namespace App\Filament\Resources\EntradaResource\Pages;

use App\Filament\Resources\EntradaResource;
use Filament\Resources\Pages\CreateRecord;

class CreateEntrada extends CreateRecord
{
    protected static string $resource = EntradaResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        logger('Validando evento_id en mutateFormDataBeforeCreate:', $data);

        if (empty($data['evento_id'])) {
            logger('ERROR: evento_id está vacío antes del abort.');
            abort(400, 'El ID del evento es requerido para crear una entrada.');
        }

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return route('filament.resources.entradas.index', ['evento_id' => $this->record->evento_id]);
    }

}
