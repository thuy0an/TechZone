<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LocationController extends BaseApiController
{
    public function provinces()
    {
        $provinces = DB::table('provinces')
            ->select('id', 'name')
            ->orderBy('id')
            ->get();

        return $this->successResponse($provinces, 'Lay danh sach tinh thanh thanh cong');
    }

    public function districts(Request $request)
    {
        $provinceId = $request->query('province_id');
        if (!$provinceId) {
            return $this->successResponse([], 'Khong co tinh thanh');
        }

        $districts = DB::table('districts')
            ->where('province_id', $provinceId)
            ->select('id', 'name')
            ->orderBy('id')
            ->get();

        return $this->successResponse($districts, 'Lay danh sach quan huyen thanh cong');
    }

    public function wards(Request $request)
    {
        $districtId = $request->query('district_id');
        if (!$districtId) {
            return $this->successResponse([], 'Khong co quan huyen');
        }

        $wards = DB::table('wards')
            ->where('district_id', $districtId)
            ->select('id', 'name', 'code')
            ->orderBy('id')
            ->get();

        return $this->successResponse($wards, 'Lay danh sach phuong xa thanh cong');
    }
}
