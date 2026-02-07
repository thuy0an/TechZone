@extends('layouts.client')

@section('title', 'TechZone - Trang chủ')

@section('content')
    <section class="bg-primary text-white py-5 text-center" style="background: linear-gradient(120deg, #a1c4fd 0%, #c2e9fb 100%);">
        <div class="container">
            <h1 class="display-4 fw-bold text-dark">Chào mừng đến TechZone</h1>
            <p class="lead text-dark mb-4">Sản phẩm công nghệ chính hãng - Giá tốt nhất thị trường</p>
            <a href="#products" class="btn btn-primary btn-lg shadow">Mua sắm ngay</a>
        </div>
    </section>

    <section id="products" class="container py-5">
        <h2 class="text-center mb-4 fw-bold text-primary">Sản phẩm nổi bật</h2>
        
        <div class="row g-4">
            @foreach($products as $product)
            <div class="col-md-3 col-6">
               
                    <div class="card h-100 shadow-sm border-0 product-card">
                        <a href="{{ route('client.product.detail', $product->id) }}" class="text-decoration-none text-dark">
                            @if(Str::startsWith($product->image, 'http'))
                                <img src="{{ $product->image }}" class="table-img" style="width: 50px; height: 50px; object-fit: cover;">
                            @else
                                <img src="{{ asset($product->image ?? 'https://via.placeholder.com/50') }}" class="card-img-top" style="height: 200px; object-fit: contain; padding: 10px">
                            @endif
                         </a>
                        <div class="card-body d-flex flex-column">
                            <h5 class="card-title text-truncate" title="{{ $product->name }}">{{ $product->name }}</h5>
                            <p class="card-text text-danger fw-bold mb-auto">
                                {{ number_format($product->current_import_price ?? 0, 0, ',', '.') }} đ
                            </p>
                            <button onclick="Cart.add({{ $product->id }})" class="btn btn-outline-primary mt-3 w-100">
                                <i class="bi bi-cart-plus"></i> Thêm vào giỏ
                            </button>
                        </div>
                    </div>
                
            </div>
            @endforeach
        </div>
    </section>
@endsection

@push('scripts')
@endpush