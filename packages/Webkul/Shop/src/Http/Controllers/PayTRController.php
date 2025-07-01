<?php

namespace Webkul\Shop\Http\Controllers;

use Illuminate\Http\Request;
use Webkul\Checkout\Facades\Cart;
use Webkul\Sales\Repositories\OrderRepository;
use Webkul\Sales\Repositories\InvoiceRepository;
use Webkul\Payment\Providers\PayTR;

class PayTRController extends Controller
{
    protected $invoiceRepository;
    protected $orderRepository;
    protected $paytr;

    public function __construct(
        OrderRepository $orderRepository,
        InvoiceRepository $invoiceRepository,
        PayTR $paytr
    ) {
        $this->orderRepository = $orderRepository;
        $this->invoiceRepository = $invoiceRepository;
        $this->paytr = $paytr;
    }

    public function redirect()
    {
        try {
            $order = $this->orderRepository->create(Cart::prepareDataForOrder());
            $token = $this->paytr->createPaymentToken($order);
            $paytr_iframe_url = 'https://www.paytr.com/odeme/guvenli/' . $token;

            return view('shop::paytr.redirect', [
                'order' => $order,
                'paytr_iframe_url' => $paytr_iframe_url,
                'token' => $token
            ]);

        } catch (\Exception $e) {
            session()->flash('error', 'Ödeme işlemi başlatılırken bir hata oluştu: ' . $e->getMessage());
            return redirect()->route('shop.checkout.cart.index');
        }
    }

    public function callback(Request $request)
    {
        try {
            $verification = $this->paytr->verifyCallback($request);
            $order = $this->orderRepository->findOrFail($verification['order_id']);

            if ($verification['status']) {
                $order->update(['status' => 'processing']);

                if ($order->canInvoice()) {
                    $invoice = $this->invoiceRepository->create($this->prepareInvoiceData($order));
                }

                return response('OK', 200);
            } else {
                $order->update(['status' => 'canceled']);
                return response('OK', 200);
            }

        } catch (\Exception $e) {
            \Log::error('PayTR Callback Error: ' . $e->getMessage());
            return response('ERROR', 400);
        }
    }

    public function success(Request $request)
    {
        try {
            Cart::deActivateCart();
            session()->flash('success', trans('shop::app.checkout.success.title'));
            return redirect()->route('shop.checkout.success');

        } catch (\Exception $e) {
            session()->flash('error', 'Bir hata oluştu: ' . $e->getMessage());
            return redirect()->route('shop.home.index');
        }
    }

    public function cancel(Request $request)
    {
        session()->flash('error', 'Ödeme işlemi iptal edildi veya başarısız oldu.');
        return redirect()->route('shop.checkout.cart.index');
    }

    private function prepareInvoiceData($order)
    {
        $invoiceData = ['order_id' => $order->id];

        foreach ($order->items as $item) {
            $invoiceData['invoice']['items'][$item->id] = $item->qty_to_invoice;
        }

        return $invoiceData;
    }

    public function checkOrderStatus(Request $request)
    {
        try {
            $orderId = $request->input('order_id');
            $order = $this->orderRepository->findOrFail($orderId);

            return response()->json([
                'status' => $order->status,
                'success' => in_array($order->status, ['processing', 'completed']),
                'redirect_url' => $order->status == 'processing' ? route('shop.checkout.success') : null
            ]);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Sipariş durumu kontrol edilemedi.'], 400);
        }
    }
}