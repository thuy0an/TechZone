<?php

namespace App\Repositories;

use App\Models\Order;
use App\Repositories\Interfaces\AdminOrderRepositoryInterface;

class AdminOrderRepository extends BaseRepository implements AdminOrderRepositoryInterface
{
    public function __construct(Order $model)
    {
        parent::__construct($model);
    }

    public function getOrdersWithFilters(array $filters, int $perPage = 15)
    {
        // Eager load quan hệ với user và chi tiết sản phẩm
        $query = $this->model->with(['user', 'details.product']);

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('id', 'like', '%' . $filters['search'] . '%')
                    ->orWhere('receiver_phone', 'like', '%' . $filters['search'] . '%')
                    ->orWhere('receiver_name', 'like', '%' . $filters['search'] . '%');
            });
        }

        if (!empty($filters['start_date'])) {
            $query->whereDate('created_at', '>=', $filters['start_date']);
        }

        // LỌC THEO NGÀY KẾT THÚC
        if (!empty($filters['end_date'])) {
            $query->whereDate('created_at', '<=', $filters['end_date']);
        }

        if (!empty($filters['payment_method'])) {
            $query->where('payment_method', $filters['payment_method']);
        }

        $this->applyLocationFilter(
            $query,
            'province_id',
            $filters['province_id'] ?? null,
            'province_name',
            $filters['province_name'] ?? null,
            ['tp ', 'tp. ', 'thanh pho ', 'tinh ']
        );

        $this->applyLocationFilter(
            $query,
            'district_id',
            $filters['district_id'] ?? null,
            'district_name',
            $filters['district_name'] ?? null,
            ['quan ', 'huyen ', 'thi xa ', 'tx. ']
        );

        $this->applyLocationFilter(
            $query,
            'ward_code',
            $filters['ward_code'] ?? null,
            'ward_name',
            $filters['ward_name'] ?? null,
            ['phuong ', 'xa ', 'thi tran ', 'tt. ']
        );

        if (!empty($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }

        return $query->orderBy('created_at', 'desc')->orderBy('id', 'desc')->paginate($perPage);
    }

    private function applyLocationFilter($query, string $idColumn, $idValue, string $nameColumn, ?string $name, array $prefixes): void
    {
        $hasId = !empty($idValue);
        $clean = $name ? trim(mb_strtolower($name)) : '';
        if (!$hasId && $clean === '') return;

        $base = $clean ? $this->stripPrefixes($clean, $prefixes) : '';
        $candidates = array_unique(array_filter([$clean, $base]));

        if ($base !== '') {
            foreach ($prefixes as $prefix) {
                $prefix = trim($prefix);
                if ($prefix === '') continue;
                $candidates[] = trim($prefix . ' ' . $base);
            }
        }
        $candidates = array_values(array_unique(array_filter($candidates)));

        $query->where(function ($q) use ($idColumn, $idValue, $nameColumn, $candidates, $base) {
            if (!empty($idValue)) {
                $q->orWhere($idColumn, $idValue);
            }
            if (!empty($candidates)) {
                $q->orWhereIn($nameColumn, $candidates);
            }
            if ($base) {
                $q->orWhere($nameColumn, 'like', '%' . $base . '%');
                $q->orWhere('shipping_address', 'like', '%' . $base . '%');
            }
        });
    }

    private function stripPrefixes(string $value, array $prefixes): string
    {
        $result = $value;
        foreach ($prefixes as $prefix) {
            $prefix = trim($prefix);
            if ($prefix === '') continue;
            if (str_starts_with($result, $prefix)) {
                $result = trim(mb_substr($result, mb_strlen($prefix)));
                break;
            }
        }
        return $result;
    }
}
