<?php

namespace App\Http\Controllers\Api\Public;

use Midtrans\Snap;
use Midtrans\Config;
use App\Models\Cart;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Routing\Controllers\HasMiddleware;

class CheckoutController extends Controller implements HasMiddleware
{
    public $response;

    public static function middleware(): array
    {
        return [
            'auth:api'
        ];
    }

    public function __construct()
    {
        Config::$serverKey = config('midtrans.server_key');
        Config::$isProduction = config('midtrans.is_production');
        Config::$isSanitized = config('midtrans.is_sanitized');
        Config::$is3ds = config('midtrans.is_3ds');
    }

    public function store(Request $request)
    {
        $customer = auth()->guard('api')->user();

        if (!$customer) {
            return response()->json([
                'success' => false,
                'message' => 'Silakan login terlebih dahulu.'
            ], 401);
        }

        $carts = Cart::with('product')
            ->where('customer_id', $customer->id)
            ->get();

        if ($carts->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Keranjang belanja masih kosong.'
            ], 422);
        }

        if (empty(config('midtrans.server_key'))) {
            return response()->json([
                'success' => false,
                'message' => 'Konfigurasi Midtrans belum tersedia.'
            ], 500);
        }

        try {
            $result = DB::transaction(function () use (
                $request,
                $customer,
                $carts
            ) {
                // Membuat invoice unik
                $invoice = 'INV-' . now()->format('YmdHis')
                    . '-' . strtoupper(\Illuminate\Support\Str::random(5));

                // Membuat transaksi
                $transaction = Transaction::create([
                    'customer_id' => $customer->id,
                    'invoice' => $invoice,
                    'province_name' => $request->province_name,
                    'city_name' => $request->city_name,
                    'district_name' => $request->district_name,
                    'subdistrict_name' => $request->subdistrict_name,
                    'zip_code' => $request->zip_code,
                    'full_address' => $request->full_address,
                    'weight' => $request->weight,
                    'total' => $request->total,
                    'status' => 'PENDING'
                ]);

                // Membuat data pengiriman
                $transaction->shipping()->create([
                    'shipping_courier' => $request->shipping_courier,
                    'shipping_service' => $request->shipping_service,
                    'shipping_cost' => $request->shipping_cost
                ]);

                $itemDetails = [];

                // Menyimpan detail produk
                foreach ($carts as $cart) {
                    if (!$cart->product) {
                        throw new \Exception(
                            'Produk pada keranjang tidak ditemukan.'
                        );
                    }

                    $price = (int) $cart->product->price;
                    $qty = (int) $cart->qty;

                    $transaction->transactionDetails()->create([
                        'product_id' => $cart->product_id,
                        'qty' => $qty,
                        'price' => $price * $qty,
                    ]);

                    $itemDetails[] = [
                        'id' => (string) $cart->product_id,
                        'price' => $price,
                        'quantity' => $qty,
                        'name' => $cart->product->title
                    ];
                }

                // Menambahkan biaya pengiriman
                $shippingCost = (int) $request->shipping_cost;

                $itemDetails[] = [
                    'id' => 'SHIPPING',
                    'price' => $shippingCost,
                    'quantity' => 1,
                    'name' => 'Shipping Cost'
                ];

                // Data pembayaran Midtrans
                $payload = [
                    'transaction_details' => [
                        'order_id' => $transaction->invoice,
                        'gross_amount' => (int) $transaction->total
                    ],
                    'customer_details' => [
                        'first_name' => $customer->name,
                        'email' => $customer->email,
                        'shipping_address' => [
                            'address' => $transaction->full_address,
                            'city' => $transaction->city_name,
                            'postal_code' => $transaction->zip_code,
                            'country_code' => 'IDN'
                        ]
                    ],
                    'item_details' => $itemDetails
                ];

                // Meminta Snap Token ke Midtrans
                $snapToken = Snap::getSnapToken($payload);

                if (!$snapToken) {
                    throw new \Exception(
                        'Gagal mendapatkan Snap Token dari Midtrans.'
                    );
                }

                // Menyimpan Snap Token
                $transaction->snap_token = $snapToken;
                $transaction->save();

                // Menghapus keranjang setelah token berhasil dibuat
                Cart::where('customer_id', $customer->id)->delete();

                return [
                    'snap_token' => $snapToken
                ];
            });

            return response()->json([
                'success' => true,
                'message' => 'Checkout Berhasil!',
                'data' => $result
            ]);

        } catch (\Throwable $e) {
            \Log::error('Checkout Midtrans Error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Checkout gagal: ' . $e->getMessage()
            ], 500);
        }
    }
}