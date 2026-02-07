@extends('layouts.client')

@section('title', $product->name)

@section('content')
<div class="container py-5">
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('home') }}">Trang chủ</a></li>
            <li class="breadcrumb-item"><a href="#">{{ $product->category->name }}</a></li>
            <li class="breadcrumb-item active" aria-current="page">{{ $product->name }}</li>
        </ol>
    </nav>

    <div class="row g-5">
        <div class="col-md-6">
            <div class="card border-0 shadow-sm p-3">
                @if(Str::startsWith($product->image, 'http'))
                    <img src="{{ $product->image }}" class="img-fluid rounded" alt="{{ $product->name }}" style="width: 100%; object-fit: contain;">
                @else
                    <img src="{{ asset($product->image ?? 'https://via.placeholder.com/500') }}" class="img-fluid rounded" alt="{{ $product->name }}">
                @endif
            </div>
        </div>

        <div class="col-md-6">
            <h1 class="fw-bold text-dark mb-2">{{ $product->name }}</h1>
            <div class="mb-3 mt-3">
                <span class="badge bg-primary px-5 py-3" style="font-size:16px">{{ $product->brand->name ?? 'No Brand' }}</span>
                <span class="text-muted ms-2">Mã SP: {{ $product->code }}</span>
            </div>

            <h2 class="text-danger fw-bold mb-4">
                {{ number_format($product->current_import_price, 0, ',', '.') }} đ
                </h2>

            <p class="text-muted mb-4">{{ $product->description ?? 'Đang cập nhật mô tả...' }}</p>
            
            @php
                // Kiểm tra: Nếu là chuỗi thì decode, nếu là mảng thì giữ nguyên
                $specs = $product->specifications;
                
                if (is_string($specs)) {
                    $specs = json_decode($specs, true);
                }
                
                if (!is_array($specs)) {
                    $specs = [];
                }
            @endphp

            {{-- Debug Log --}}
            {{-- <div class="alert alert-warning">
                Debug Type: {{ gettype($product->specifications) }} <br>
                Debug Value: {{ var_export($product->specifications, true) }}
            </div> --}}

            @if(!empty($specs))
                <div class="bg-light p-3 rounded mb-4">
                    <h6 class="fw-bold">Thông số nổi bật:</h6>
                    <ul class="list-unstyled mb-0">
                        @foreach($specs as $key => $value)
                            <li class="d-flex justify-content-between border-bottom py-2">
                                <span>{{ $key }}</span>
                                <span class="fw-medium">{{ $value }}</span>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="d-flex gap-3">
                <div class="input-group d-flex" style="width: 200px;">
                    <button class="btn btn-secondary" type="button" onclick="changeQty(-1)">-</button>
                    <input type="number" id="buy-qty" class="form-control text-center" value="1" min="1">
                    <button class="btn btn-secondary" type="button" onclick="changeQty(1)">+</button>
                </div>
                <button id="btn-add-to-cart" 
                        onclick="Cart.add({{ $product->id }}, document.getElementById('buy-qty').value)" 
                        class="btn btn-primary flex-grow-1 fw-bold">
                    <i class="bi bi-cart-plus me-2"></i> THÊM VÀO GIỎ
                </button>
            </div>
        </div>
    </div>

    @if($relatedProducts->count() > 0)
    <div class="mt-5">
        <h3 class="fw-bold mb-4">Sản phẩm liên quan</h3>
        <div class="row g-4">
            @foreach($relatedProducts as $rel)
            <div class="col-md-3 col-6">
                <div class="card h-100 shadow-sm border-0 product-card">
                    <a href="{{ route('client.product.detail', $rel->id) }}" class="text-decoration-none text-dark">
                        @if(Str::startsWith($rel->image, 'http'))
                            <img src="{{ $rel->image }}" class="card-img-top p-3" style="height: 200px; object-fit: contain;">
                        @else
                            <img src="{{ asset($rel->image ?? 'https://via.placeholder.com/300') }}" class="card-img-top p-3" style="height: 200px; object-fit: contain;">
                        @endif
                        <div class="card-body">
                            <h6 class="card-title text-truncate">{{ $rel->name }}</h6>
                            <p class="text-danger fw-bold mb-0">{{ number_format($rel->current_import_price, 0, ',', '.') }} đ</p>
                        </div>
                    </a>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif
</div>
@endsection

@push('scripts')
<script>
    function changeQty(amount) {
        const input = document.getElementById('buy-qty');
        let val = parseInt(input.value) + amount;
        if (val < 1) val = 1;
        input.value = val;
    }

    function addToCartDetail(id) {
        const qty = document.getElementById('buy-qty').value;
        
        alert(`Đã thêm ${qty} sản phẩm vào giỏ hàng (Chức năng đang hoàn thiện)`);
    }
</script>
@endpush