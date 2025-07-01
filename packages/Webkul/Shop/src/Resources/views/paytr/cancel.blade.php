@extends('shop::layouts.master')

@section('page_title')
    {{ __('Ödeme İptal Edildi') }}
@endsection

@section('content-wrapper')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-6">
                <div class="card border-warning">
                    <div class="card-body text-center">
                        <div class="cancel-icon mb-4">
                            <i class="fas fa-exclamation-triangle text-warning" style="font-size: 4rem;"></i>
                        </div>

                        <h2 class="text-warning mb-3">{{ __('Ödeme İptal Edildi') }}</h2>

                        <p class="lead mb-4">
                            {{ __('Ödeme işlemi iptal edildi veya başarısız oldu. Lütfen tekrar deneyiniz.') }}
                        </p>

                        <div class="action-buttons">
                            <a href="{{ route('shop.checkout.cart.index') }}" class="btn btn-primary me-3">
                                <i class="fas fa-shopping-cart"></i> {{ __('Sepete Dön') }}
                            </a>

                            <a href="{{ route('shop.home.index') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-home"></i> {{ __('Ana Sayfa') }}
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection