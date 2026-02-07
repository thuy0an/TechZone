@extends('layouts.admin')

@section('title', 'Quản lý Sản phẩm')

@section('content')
<div class="p-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="h3 mb-0 text-gray-800">📦 Danh Sách Sản Phẩm</h2>
        <button onclick="openCreateModal()" class="btn btn-primary">
            <i class="bi bi-plus-lg"></i> Thêm mới
        </button>
    </div>

    <div class="card mb-4 shadow-sm">
        <div class="card-body">
            <form action="{{ route('admin.products.index') }}" method="GET" class="input-group">
                <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
                <input type="text" name="search" class="form-control border-start-0" 
                       placeholder="Tìm kiếm theo tên hoặc mã SKU..." 
                       value="{{ request('search') }}">
                <button class="btn btn-outline-secondary" type="submit">Tìm kiếm</button>
            </form>
        </div>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-striped align-middle mb-0">
                    <thead class="table-light">
                        <tr class="text-center">
                            <th width="5%">ID</th>
                            <th width="15%">Ảnh</th>
                            <th width="10%">Mã SKU</th>
                            <th width="20%">Tên sản phẩm</th>
                            <th width="15%">Giá nhập</th>
                            <th width="10%">Tồn kho</th>
                            <th width="10%">Trạng thái</th>
                            <th width="20%" class="text-center">Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($products as $product)
                        <tr id="row-{{ $product->id }}" class="text-center">
                            <td>{{ $product->id }}</td>
                            <td>
                                @if(Str::startsWith($product->image, 'http'))
                                    <img src="{{ $product->image }}" class="table-img" style="width: 100px; height: 75px; object-fit: contain;">
                                @else
                                    <img src="{{ asset($product->image ?? 'https://via.placeholder.com/50') }}" class="table-img" style="width: 125px; height: 75px; object-fit: contain;">
                                @endif
                            </td>
                            <td class="fw-bold text-primary">{{ $product->code }}</td>
                            <td>
                                <div class="fw-medium">{{ $product->name }}</div>
                                <small class="text-muted">
                                    {{ $product->category->name ?? 'N/A' }} | {{ $product->brand->name ?? 'N/A' }}
                                </small>
                            </td>
                            <td class="text-danger fw-bold">
                                {{ number_format($product->current_import_price, 0, ',', '.') }} đ
                            </td>
                            <td>
                                @if($product->stock_quantity > 0)
                                    <span class="badge bg-success bg-opacity-10 text-success">{{ $product->stock_quantity }}</span>
                                @else
                                    <span class="badge bg-danger bg-opacity-10 text-danger">Hết hàng</span>
                                @endif
                            </td>
                            <td>
                                {!! $product->is_hidden ? '<span class="badge bg-secondary px-3 py-2">Ẩn</span>' : '<span class="badge bg-primary px-3 py-2">Hiện</span>' !!}
                            </td>
                            <td class="text-center">
                                <button class="btn btn-sm btn-warning" 
                                        onclick='openEditModal(@json($product))'>
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <button class="btn btn-sm btn-danger" onclick="deleteProduct({{ $product->id }})">
                                    <i class="bi bi-trash"></i>
                                </button>
                                <button class="btn btn-sm btn-info text-white me-1" onclick='openViewModal(@json($product))'>
                                    <i class="bi bi-eye"></i>
                                </button>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center py-4 text-muted">
                                <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                Không tìm thấy sản phẩm nào
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            <div class="d-flex justify-content-end p-3">
                {{ $products->links('pagination::bootstrap-5') }}
            </div>
        </div>
    </div>
</div>

{{-- MODAL SỬA SẢN PHẨM --}}
<div class="modal fade" id="productModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-warning">
                <h5 class="modal-title" id="modalTitle">Cập nhật sản phẩm</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="productForm">
                    <input type="hidden" id="p-id" name="id">
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Danh mục <span class="text-danger">*</span></label>
                            <select id="p-category" name="category_id" class="form-select" required>
                                <option value="">-- Chọn danh mục --</option>
                                @foreach($categories as $cat)
                                    <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Thương hiệu</label>
                            <select id="p-brand" name="brand_id" class="form-select">
                                <option value="">-- Chọn thương hiệu --</option>
                                @foreach($brands as $brand)
                                    <option value="{{ $brand->id }}">{{ $brand->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Tên sản phẩm <span class="text-danger">*</span></label>
                            <input type="text" id="p-name" name="name" class="form-control" placeholder="Ví dụ: Laptop Dell XPS 13" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Mã SKU <span class="text-danger">*</span></label>
                            <input type="text" id="p-code" name="code" class="form-control" placeholder="Ví dụ: DELL-XPS13-2024" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Mô tả chi tiết</label>
                        <textarea id="p-description" name="description" class="form-control" rows="3" placeholder="Mô tả sản phẩm..."></textarea>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label class="form-label">Giá nhập (VNĐ) <span class="text-danger">*</span></label>
                            <input type="number" id="p-price" name="current_import_price" class="form-control" min="0" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Tồn kho <span class="text-danger">*</span></label>
                            <input type="number" id="p-stock" name="stock_quantity" class="form-control" min="0" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">% Lãi riêng (Nếu có)</label>
                            <input type="number" id="p-profit" name="specific_profit_margin" class="form-control" step="0.1" min="0" max="100" placeholder="VD: 10.5">
                            <div class="form-text small">Để trống sẽ dùng lãi mặc định Danh mục.</div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Ảnh sản phẩm</label>
                        <input type="file" id="p-image" name="image" class="form-control" accept="image/*">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Thông số kỹ thuật (JSON)</label>
                        <textarea id="p-specs" name="specifications" class="form-control font-monospace" rows="3" placeholder='{"CPU": "Core i5", "RAM": "8GB"}'></textarea>
                        <div class="form-text small">Nhập dạng JSON hợp lệ.</div>
                    </div>

                    <div class="row mb-3 border rounded p-2 mx-0 bg-light">
                        <div class="col-md-6">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="p-has-serial" name="has_serial" value="1" checked>
                                <label class="form-check-label fw-bold" for="p-has-serial">Quản lý theo Serial Number</label>
                                <div class="form-text small">Dùng cho Laptop, Điện thoại (cần bảo hành).</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="p-hidden" name="is_hidden" value="1">
                                <label class="form-check-label fw-bold text-danger" for="p-hidden">Ẩn sản phẩm khỏi Website</label>
                            </div>
                        </div>
                    </div>

                    <div class="text-end">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                        <button type="submit" class="btn btn-primary px-4 fw-bold"><i class="bi bi-save"></i> Lưu dữ liệu</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>


{{-- MODAL XEM CHI TIẾT SẢN PHẨM --}}
<div class="modal fade" id="viewProductModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">Chi tiết sản phẩm</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-4 text-center">
                        <img id="v-image" src="" class="img-fluid rounded border mb-3" style="max-height: 250px;">
                    </div>
                    <div class="col-md-8">
                        <h4 id="v-name" class="fw-bold"></h4>
                        <p class="text-muted">SKU: <span id="v-code"></span></p>
                        
                        <table class="table table-sm table-bordered">
                            <tr>
                                <th width="30%">Danh mục</th>
                                <td id="v-category"></td>
                            </tr>
                            <tr>
                                <th>Thương hiệu</th>
                                <td id="v-brand"></td>
                            </tr>
                            <tr>
                                <th>Giá nhập</th>
                                <td class="text-danger fw-bold" id="v-price"></td>
                            </tr>
                            <tr>
                                <th>Tồn kho</th>
                                <td id="v-stock"></td>
                            </tr>
                            <tr>
                                <th>Lãi riêng</th>
                                <td id="v-profit"></td>
                            </tr>
                        </table>
                    </div>
                </div>
                
                <div class="mt-3">
                    <h6 class="fw-bold border-bottom pb-2">Mô tả</h6>
                    <p id="v-desc" class="text-muted small"></p>
                </div>

                <div class="mt-3">
                    <h6 class="fw-bold border-bottom pb-2">Thông số kỹ thuật</h6>
                    <div id="v-specs" class="bg-light p-2 rounded small font-monospace"></div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
    <script>
        const ProductConfig = {
            routes: {
                store: "{{ route('admin.products.store') }}",
                update: "{{ route('admin.products.update', ':id') }}",
                delete: "{{ route('admin.products.destroy', ':id') }}"
            }
        };
    </script>
    
    <script src="{{ asset('js/admin/products.js') }}"></script>
@endpush