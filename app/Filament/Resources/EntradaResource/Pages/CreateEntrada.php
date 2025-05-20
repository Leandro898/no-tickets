<?php

namespace App\Filament\Resources\EntradaResource\Pages;

use App\Filament\Resources\EntradaResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Request;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use App\Filament\Resources\EventoResource;
use \Filament\Forms\Components\Toggle;


class CreateEntrada extends CreateRecord
{
    protected static string $resource = EntradaResource::class;

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                \Filament\Forms\Components\Hidden::make('evento_id')
                    ->default(fn (): ?int => request()->query('evento_id')),

                \Filament\Forms\Components\TextInput::make('nombre')->required(),
                \Filament\Forms\Components\TextInput::make('precio')
                    ->numeric()
                    ->required(),
                \Filament\Forms\Components\TextInput::make('stock_inicial')
                    ->numeric()
                    ->required(),
                Toggle::make('visible')
                    ->label('Visible')
                    ->default(true),
                // Otros campos...
            ]);
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Obtener el evento_id de la URL o de los datos del formulario
        $eventoId = $this->data['evento_id'] ?? request()->query('evento_id');
        
        if (!$eventoId) {
            Notification::make()
                ->title('Error')
                ->body('Debe seleccionar un evento válido')
                ->danger()
                ->send();
                
            $this->halt(); // Detiene el proceso de creación
        }

        $data['evento_id'] = $eventoId;
        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return EventoResource::getUrl('gestionar-entradas', ['record' => $this->record->evento_id]);
    }

    // protected function getRedirectUrl(): string
    // {
    //     // Redirige a la página de gestión de entradas del evento
    //     return "/admin/eventos/{$this->record->evento_id}/gestionar-entradas";
    // }

    protected function afterCreate(): void
    {
        Notification::make()
            ->title('Entrada creada exitosamente')
            ->success()
            ->send();
    }
}