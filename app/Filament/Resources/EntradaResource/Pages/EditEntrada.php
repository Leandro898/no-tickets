<?php

namespace App\Filament\Resources\EntradaResource\Pages;

use App\Filament\Resources\EntradaResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Forms\Form;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Hidden;
use App\Filament\Resources\EventoResource;

class EditEntrada extends EditRecord
{
    protected static string $resource = EntradaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            //Actions\DeleteAction::make(),
        ];
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Hidden::make('evento_id'),
                TextInput::make('nombre')->required(),
                TextInput::make('precio')
                    ->numeric()
                    ->required(),
                TextInput::make('stock_inicial')
                    ->numeric()
                    ->required(),
                Toggle::make('visible')
                    ->label('Visible'),
            ]);
    }

    protected function getRedirectUrl(): string
    {
        // Redirige a la página de gestión de entradas del evento al que pertenece la entrada editada
        return EventoResource::getUrl('gestionar-entradas', ['record' => $this->record->evento_id]);
    }
}