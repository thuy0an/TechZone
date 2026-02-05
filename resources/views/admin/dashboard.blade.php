@extends('layouts.admin')

@section('title', 'Dashboard')

@section('content')
<div class="p-4">
    <h2 class="mb-4">📊 Tổng quan hệ thống</h2>
    <div class="row g-4">
        <div class="col-md-3">
            <div class="card border-primary border-start border-4 shadow-sm">
                <div class="card-body">
                    <h6 class="text-uppercase text-primary fw-bold">Doanh thu</h6>
                    <h3>50,000,000 đ</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-success border-start border-4 shadow-sm">
                <div class="card-body">
                    <h6 class="text-uppercase text-success fw-bold">Đơn hàng mới</h6>
                    <h3>12</h3>
                </div>
            </div>
        </div>
        </div>
</div>
@endsection