<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Evento;
use App\Models\Entrada; // Tu modelo Entrada
use App\Models\Order; // Tu modelo de Orden que ya creamos
use App\Models\PurchasedTicket; // ¡AHORA USAMOS ESTE MODELO!
use Illuminate\Support\Str; // Para UUID
use MercadoPago\Preference;
use MercadoPago\Item;
use MercadoPago\SDK;
use Illuminate\Support\Facades\DB; // Para transacciones de base de datos
use Carbon\Carbon; // Para manejo de fechas
use Illuminate\Validation\ValidationException; // Para tus validaciones
use SimpleSoftwareIO\QrCode\Facades\QrCode; // Para generar QR
use Illuminate\Support\Facades\Storage; // Para guardar el archivo QR


class CompraEntradaController extends Controller
{
    public function __construct()
    {
        SDK::setAccessToken(config('mercadopago.access_token'));
    }

    public function show(Evento $evento)
    {
        $now = Carbon::now();
        $entradas = $evento->entradas()
            ->where('stock_actual', '>', 0)
            ->where(function ($query) use ($now) {
                $query->whereNull('disponible_desde')
                      ->orWhere('disponible_desde', '<=', $now);
            })
            ->where(function ($query) use ($now) {
                $query->whereNull('disponible_hasta')
                      ->orWhere('disponible_hasta', '>=', $now);
            })
            ->get();

        return view('comprar', compact('evento', 'entradas'));
    }

    public function store(Request $request, Evento $evento)
    {
        try {
            $validatedData = $request->validate([
                'nombre' => 'required|string',
                'email' => 'required|email',
                'cantidades' => 'required|array|min:1',
                'cantidades.*' => 'integer|min:0',
                'buyer_phone' => 'nullable|string|max:50', // Añadimos si tu formulario lo tiene
                'buyer_dni' => 'nullable|string|max:20',   // Añadimos si tu formulario lo tiene
            ]);

            $totalAmount = 0;
            $itemsForMercadoPago = [];
            $entradasSeleccionadas = [];

            foreach ($validatedData['cantidades'] as $entradaId => $cantidad) {
                if ($cantidad > 0) {
                    $entrada = Entrada::findOrFail($entradaId);

                    if ($entrada->stock_actual < $cantidad) {
                        throw ValidationException::withMessages(['cantidades.' . $entradaId => "No hay suficiente stock disponible para '{$entrada->nombre}'. Stock actual: {$entrada->stock_actual}."]);
                    }
                    if ($entrada->max_por_compra && $cantidad > $entrada->max_por_compra) {
                        throw ValidationException::withMessages(['cantidades.' . $entradaId => "No puedes comprar más de " . $entrada->max_por_compra . " entradas de tipo '{$entrada->nombre}'."]);
                    }

                    $now = Carbon::now();
                    if ($entrada->disponible_desde && $now->lt($entrada->disponible_desde)) {
                        throw ValidationException::withMessages(['cantidades.' . $entradaId => "La venta para '{$entrada->nombre}' aún no ha comenzado."]);
                    }
                    if ($entrada->disponible_hasta && $now->gt($entrada->disponible_hasta)) {
                        throw ValidationException::withMessages(['cantidades.' . $entradaId => "La venta para '{$entrada->nombre}' ha finalizado."]);
                    }

                    $subtotal = $entrada->precio * $cantidad;
                    $totalAmount += $subtotal;

                    $item = new Item();
                    $item->title = $entrada->nombre . ' - ' . $evento->nombre;
                    $item->quantity = $cantidad;
                    $item->unit_price = (float)$entrada->precio;
                    $item->currency_id = 'ARS';
                    $item->id = (string)$entrada->id; // Pasamos el ID de tu Entrada como ID del ítem en MP
                    $itemsForMercadoPago[] = $item;

                    $entradasSeleccionadas[] = [
                        'entrada_id' => $entrada->id,
                        'cantidad' => $cantidad,
                        'precio_unitario' => $entrada->precio,
                    ];
                }
            }

            if ($totalAmount === 0) {
                throw ValidationException::withMessages(['cantidades' => 'Debes seleccionar al menos una entrada.']);
            }

            DB::beginTransaction();

            $order = Order::create([
                'event_id' => $evento->id,
                'buyer_full_name' => $validatedData['nombre'],
                'buyer_email' => $validatedData['email'],
                'buyer_phone' => $request->input('buyer_phone'), // Asegúrate de que estos campos existan en tu formulario
                'buyer_dni' => $request->input('buyer_dni'),     // Asegúrate de que estos campos existan en tu formulario
                'total_amount' => $totalAmount,
                'payment_status' => 'pending',
                'items_data' => json_encode($entradasSeleccionadas),
            ]);

            $preference = new Preference();
            $preference->items = $itemsForMercadoPago;

            $preference->notification_url = config('mercadopago.notification_url');
            $preference->back_urls = [
                "success" => route('purchase.success', ['order' => $order->id]),
                "failure" => route('purchase.failure', ['order' => $order->id]),
                "pending" => route('purchase.pending', ['order' => $order->id]),
            ];
            $preference->auto_return = "approved";
            $preference->external_reference = $order->id;
            $preference->save();

            $order->mp_preference_id = $preference->id;
            $order->save();

            DB::commit();

            return redirect()->away($preference->init_point);

        } catch (ValidationException $e) {
            DB::rollBack();
            return redirect()->back()->withErrors($e->validator->getMessageBag())->withInput();
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error al iniciar compra con Mercado Pago: ' . $e->getMessage(), ['exception' => $e]);
            return redirect()->back()->withErrors(['error' => 'Hubo un error al procesar tu compra. Por favor, inténtalo de nuevo.'])->withInput();
        }
    }

    public function handleWebhook(Request $request)
    {
        \Log::info('Webhook de Mercado Pago recibido.', $request->all());

        if ($request->input('topic') === 'payment') {
            $paymentId = $request->input('id');

            try {
                $payment = SDK::get('/v1/payments/' . $paymentId);

                if ($payment->status === 200) {
                    $paymentData = $payment->response;
                    $externalReference = $paymentData->external_reference;

                    $order = Order::find($externalReference);

                    if ($order && $order->payment_status !== 'approved') {
                        DB::beginTransaction();
                        try {
                            $order->payment_status = $paymentData->status;
                            $order->mp_payment_id = $paymentData->id;
                            $order->save();

                            if ($order->payment_status === 'approved') {
                                $itemsData = json_decode($order->items_data, true);

                                if (is_array($itemsData)) {
                                    foreach ($itemsData as $item) {
                                        $entrada = Entrada::find($item['entrada_id']);
                                        $cantidad = $item['cantidad'];

                                        if ($entrada) {
                                            for ($i = 0; $i < $cantidad; $i++) {
                                                $uniqueCode = (string) Str::uuid();
                                                $qrPath = 'qrcodes/' . $uniqueCode . '.png';
                                                $qrContent = route('ticket.validate', ['code' => $uniqueCode]);

                                                QrCode::format('png')->size(300)->generate($qrContent, storage_path('app/public/' . $qrPath));

                                                PurchasedTicket::create([ // ¡USAMOS PurchasedTicket!
                                                    'order_id' => $order->id,
                                                    'entrada_id' => $entrada->id,
                                                    'unique_code' => $uniqueCode,
                                                    'qr_path' => $qrPath,
                                                    'status' => 'valid',
                                                ]);
                                            }

                                            $entrada->decrement('stock_actual', $cantidad);
                                            // También podrías incrementar sold_quantity si lo tienes
                                            // $entrada->increment('sold_quantity', $cantidad);
                                        } else {
                                            \Log::warning('Webhook: Entrada ID ' . $item['entrada_id'] . ' no encontrada para la orden ' . $order->id);
                                        }
                                    }
                                } else {
                                    \Log::error('Webhook: items_data no es un array válido para la orden ' . $order->id);
                                }
                            }

                            DB::commit();
                        } catch (\Exception $e) {
                            DB::rollBack();
                            \Log::error('Error procesando webhook de Mercado Pago para orden ' . $order->id . ': ' . $e->getMessage(), ['exception' => $e]);
                        }
                    } elseif ($order && $order->payment_status === 'approved') {
                        \Log::info('Webhook: Orden ' . $order->id . ' ya estaba aprobada. Ignorando.', $request->all());
                    } else {
                        \Log::warning('Webhook: Orden no encontrada o ya procesada con external_reference ' . $externalReference);
                    }
                }
            } catch (\Exception $e) {
                \Log::error('Error al procesar el pago de Mercado Pago con ID ' . $paymentId . ': ' . $e->getMessage(), ['exception' => $e]);
            }
        }

        return response()->json(['status' => 'success'], 200);
    }

    public function success(Request $request, Order $order)
    {
        return view('purchase.success', compact('order'));
    }

    public function failure(Request $request, Order $order)
    {
        return view('purchase.failure', compact('order'));
    }

    public function pending(Request $request, Order $order)
    {
        return view('purchase.pending', compact('order'));
    }

    public function index()
    {
        // Asegúrate de que este método muestre los PurchasedTickets
        $tickets = PurchasedTicket::with('entrada.evento')->latest()->paginate(10);
        return view('tickets.index', compact('tickets'));
    }
}