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
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Checkbox;

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

                TextInput::make('nombre')
                    ->required()
                    ->maxLength(255)
                    ->label('Nombre de la Entrada')
                    ->placeholder('Ej: Entrada General, VIP, Early Bird'),

                Textarea::make('descripcion')
                    ->rows(2)
                    ->maxLength(200)
                    ->columnSpanFull()
                    ->label('Descripción'),

                TextInput::make('precio')
                    ->numeric()
                    ->required()
                    ->step(0.01)
                    ->prefix('ARS$')
                    ->label('Precio'),

                TextInput::make('stock_inicial')
                    ->numeric()
                    ->required()
                    ->minValue(0)
                    ->label('Stock Inicial (Cantidad total disponible)'),

                TextInput::make('stock_actual')
                    ->numeric()
                    ->label('Stock Actual (Cantidad restante para vender)'),

                TextInput::make('max_por_compra')
                    ->numeric()
                    ->nullable()
                    ->minValue(1)
                    ->label('Máximo por Compra')
                    ->placeholder('Dejar vacío para ilimitado por compra'),

                // TUS CAMPOS DE FECHA DE DISPONIBILIDAD
                DateTimePicker::make('disponible_desde')
                    ->label('Disponible Desde')
                    ->nullable()
                    ->seconds(false),

                DateTimePicker::make('disponible_hasta')
                    ->label('Disponible Hasta')
                    ->nullable()
                    ->seconds(false),

                Checkbox::make('valido_todo_el_evento')
                    ->label('Este producto es válido para cualquier día del evento'),

                Toggle::make('visible') // Este campo 'visible' lo tenías aquí y no en EntradaResource. Ahora estará en ambos.
                    ->label('Visible')
                    ->default(true),
            ]);
    }

    protected function getRedirectUrl(): string
    {
        // Redirige a la página de gestión de entradas del evento al que pertenece la entrada editada
        return EventoResource::getUrl('gestionar-entradas', ['record' => $this->record->evento_id]);
    }

    public function getBreadcrumbs(): array
    {
        return [];
    }
}