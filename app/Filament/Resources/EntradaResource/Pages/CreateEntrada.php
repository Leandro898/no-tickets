<?php

namespace App\Filament\Resources\EntradaResource\Pages;

use App\Filament\Resources\EntradaResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Request;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use App\Filament\Resources\EventoResource;
use \Filament\Forms\Components\Toggle;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Get;
use Filament\Actions\Action;


class CreateEntrada extends CreateRecord
{
    protected static string $resource = EntradaResource::class;

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                // TU CAMPO HIDDEN DE EVENTO_ID (se mantiene aquí, no se quita)
                Hidden::make('evento_id')
                    ->default(fn (): ?int => request()->query('evento_id'))
                    ->required(), // Es requerido porque estás filtrando por él.

                TextInput::make('nombre')
                    ->required()
                    ->maxLength(255)
                    ->label('Nombre de la Entrada')
                    ->placeholder('Ej: Entrada General, VIP, Early Bird'),

                Textarea::make('descripcion')
                    ->rows(3)
                    ->maxLength(500)
                    ->columnSpanFull()
                    ->label('Descripción'),

                TextInput::make('precio')
                    ->numeric()
                    ->required()
                    ->step(0.01)
                    ->prefix('ARS$')
                    ->label('Precio'),

                // TUS CAMPOS DE STOCK
                // TextInput::make('stock_inicial')
                //     ->numeric()
                //     ->minValue(0)
                //     ->label('Stock Inicial (Cantidad total disponible)'),
                    
                TextInput::make('stock_actual')
                    ->numeric()
                    ->required()
                    ->label('Stock Inicial'),

                TextInput::make('max_por_compra')
                    ->numeric()
                    ->nullable()
                    ->minValue(1)
                    ->label('Máximo por Compra')
                    ->placeholder('Dejar vacío para ilimitado por compra'),

                Checkbox::make('valido_todo_el_evento')
                    ->label('Este producto es válido para cualquier día del evento')
                    ->default(true)
                    ->reactive(),

                DateTimePicker::make('disponible_desde')
                    ->label('Válido desde')
                    ->nullable()
                    ->seconds(false)
                    ->hidden(fn($get) => $get('valido_todo_el_evento')),

                DateTimePicker::make('disponible_hasta')
                    ->label('Válido hasta')
                    ->nullable()
                    ->seconds(false)
                    ->hidden(fn($get) => $get('valido_todo_el_evento')),

            Toggle::make('sold_out')
                ->label('Sold out')
                ->default(false)
                ->reactive()
                ->hidden(fn(Get $get) => ! $get('visible'))
                ->helperText(
                    fn(Get $get) => $get('sold_out')
                        ? 'Este producto ya no está disponible para la venta'
                        : 'Tus clientes podrán comprar este producto'
                ),

            Toggle::make('visible')
                ->label('Visible')
                ->default(true)
                ->reactive()
                ->helperText(
                    fn(Get $get) => $get('visible')
                        ? 'Tus clientes podrán ver este producto'
                        : 'Este producto no es visible para tus clientes'
                )
                ->columnSpanFull(),
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

        // Asegúrate de que stock_actual se inicialice si stock_inicial está presente
        if (isset($data['stock_inicial']) && !isset($data['stock_actual'])) {
            $data['stock_actual'] = $data['stock_inicial'];
        }

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        // Aseguramos que $this->record->evento_id exista. Si la creación falló, $this->record podría ser nulo.
        $eventoId = $this->record ? $this->record->evento_id : request()->query('evento_id');
        return EventoResource::getUrl('gestionar-entradas', ['record' => $eventoId]);
    }

    // protected function getRedirectUrl(): string
    // {
    //     // Redirige a la página de gestión de entradas del evento
    //     return "/admin/eventos/{$this->record->evento_id}/gestionar-entradas";
    // }

    // protected function afterCreate(): void
    // {
    //     Notification::make()
    //         ->title('Entrada creada exitosamente')
    //         ->success()
    //         ->send();
    // }

    public function getBreadcrumbs(): array
    {
        return [];
    }

    public function getTitle(): string
    {
        return 'Crear Entrada / Ticket';
    }

    /**
     * Aquí redefinimos las acciones del formulario:
     * – getCreateFormAction(): el botón principal (“Crear”)
     * – getCreateAndCreateAnotherFormAction(): “Crear y crear otra”
     * – getCancelFormAction(): “Cancelar”
     */
    protected function getFormActions(): array
    {
        return [
            $this
                ->getCreateFormAction()
                ->label('Crear Entrada')
                ->color('success'),

            $this
                ->getCancelFormAction()
                ->label('Cancelar'),
        ];
    }

    // Notificaciones al crearse una entrada
    // protected function getCreatedNotificationTitle(): ?string
    // {
    //     return '¡Entrada Generada!';
    // }

    protected function getCreatedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('¡Entrada Generada!')
            ->body('La entrada se creó correctamente y ya está disponible.');
    }

    /* BOTON PARA REGRESAR*/
    protected function getHeaderActions(): array
    {
        return [
            Action::make('volver')
                ->label('Volver a gestionar entradas')
                ->icon('heroicon-o-arrow-left')
                ->url(fn() => '/admin/eventos/' . request()->query('evento_id') . '/gestionar-entradas')
                ->extraAttributes(['class' => 'btn-volver']),
        ];
    }
}