@extends('shop::layouts.master')

@section('page_title')
    {{ __('Ödeme Başarılı') }}
@endsection

@section('content-wrapper')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-6">
                <div class="card border-success">
                    <div class="card-body text-center">
                        <div class="success-icon mb-4">
                            <i class="fas fa-check-circle text-success" style="font-size: 4rem;"></i>
                        </div>

                        <h2 class="text-success mb-3">{{ __('Ödeme Başarılı!') }}</h2>

                        <p class="lead mb-4">
                            {{ __('Siparişiniz başarıyla alınmıştır. Kısa süre içinde size ulaşacağız.') }}
                        </p>

                        <div class="order-details mb-4">
                            @if(session('order'))
                                <p><strong>Sipariş No:</strong> #{{ session('order.increment_id') }}</p>
                                <p><strong>Toplam Tutar:</strong> {{ core()->formatPrice(session('order.grand_total')) }}</p>
                            @endif
                        </div>

                        <div class="action-buttons">
                            <a href="{{ route('shop.home.index') }}" class="btn btn-primary me-3">
                                <i class="fas fa-home"></i> {{ __('Ana Sayfa') }}
                            </a>

                            <a href="{{ route('customer.account.orders.index') }}" class="btn btn-outline-primary">
                                <i class="fas fa-list"></i> {{ __('Siparişlerim') }}
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection