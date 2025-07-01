@extends('shop::layouts.master')

@section('page_title')
    {{ __('PayTR Ödeme') }}
@endsection

@section('content-wrapper')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header">
                        <h4 class="mb-0">{{ __('Ödeme İşlemi') }}</h4>
                    </div>
                    <div class="card-body">
                        <div class="payment-info mb-4">
                            <div class="row">
                                <div class="col-md-6">
                                    <p><strong>Sipariş No:</strong> #{{ $order->increment_id }}</p>
                                    <p><strong>Toplam Tutar:</strong> {{ core()->formatPrice($order->grand_total) }}</p>
                                </div>
                                <div class="col-md-6">
                                    <p><strong>Müşteri:</strong> {{ $order->customer_full_name }}</p>
                                    <p><strong>E-posta:</strong> {{ $order->customer_email }}</p>
                                </div>
                            </div>
                        </div>

                        <div class="payment-frame">
                            <div class="loading-message text-center mb-3">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="sr-only">Yükleniyor...</span>
                                </div>
                                <p class="mt-2">PayTR güvenli ödeme sayfası yükleniyor...</p>
                            </div>

                            <iframe id="paytr-iframe" src="{{ $paytr_iframe_url }}" width="100%" height="600"
                                frameborder="0" scrolling="auto" style="display: none;">
                            </iframe>
                        </div>

                        <div class="payment-actions mt-3">
                            <a href="{{ route('shop.checkout.cart.index') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Sepete Dön
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const iframe = document.getElementById('paytr-iframe');
            const loadingMessage = document.querySelector('.loading-message');

            iframe.onload = function () {
                loadingMessage.style.display = 'none';
                iframe.style.display = 'block';
            };

            const orderId = {{ $order->id }};
            let checkCount = 0;
            const maxChecks = 60;

            function checkOrderStatus() {
                if (checkCount >= maxChecks) return;

                fetch('/paytr/check-status', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({ order_id: orderId })
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success && data.redirect_url) {
                            window.location.href = data.redirect_url;
                        } else if (data.status === 'canceled') {
                            window.location.href = '/paytr/cancel';
                        } else {
                            checkCount++;
                            setTimeout(checkOrderStatus, 5000);
                        }
                    })
                    .catch(error => {
                        checkCount++;
                        setTimeout(checkOrderStatus, 5000);
                    });
            }

            setTimeout(checkOrderStatus, 10000);
        });
    </script>
@endpush