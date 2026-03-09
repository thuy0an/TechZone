<?php

namespace App\Services;

use App\Repositories\Interfaces\BrandRepositoryInterface;
use App\Services\Interfaces\BrandServiceInterface;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class BrandService extends BaseService implements BrandServiceInterface
{
    protected BrandRepositoryInterface $brandRepository;

    public function __construct(BrandRepositoryInterface $brandRepository)
    {
        parent::__construct($brandRepository);
        $this->brandRepository = $brandRepository;
    }

    /**
     * Kiểm tra unique name và xử lý upload logo
     */
    protected function beforeCreate(array $data): array
    {
        if ($this->brandRepository->existsByName($data['name'])) {
            throw ValidationException::withMessages([
                'name' => ['Tên thương hiệu đã tồn tại']
            ]);
        }

        if (isset($data['logo']) && $data['logo'] instanceof UploadedFile) {
            $data['logo'] = $this->uploadLogo($data['logo']);
        }

        return $data;
    }

    /**
     * Kiểm tra unique name và xử lý upload/xóa logo
     */
    protected function beforeUpdate(int $id, array $data): array
    {
        if (isset($data['name']) &&
            $this->brandRepository->existsByName($data['name'], $id)) {
            throw ValidationException::withMessages([
                'name' => ['Tên thương hiệu đã tồn tại']
            ]);
        }

        if (isset($data['logo']) && $data['logo'] instanceof UploadedFile) {
            // Xóa logo cũ nếu có
            $brand = $this->brandRepository->findByIdOrFail($id);
            if ($brand->logo) {
                $this->deleteLogo($brand->logo);
            }
            $data['logo'] = $this->uploadLogo($data['logo']);
        }

        return $data;
    }

    /**
     * Kiểm tra có sản phẩm thuộc brand không
     */
    protected function beforeDelete(int $id): void
    {
        $brand = $this->brandRepository->findByIdOrFail($id);

        if ($brand->products()->exists()) {
            throw new \Exception(
                'Không thể xóa thương hiệu này vì đang có sản phẩm thuộc thương hiệu này',
                409
            );
        }
    }

    /**
     * Override delete để xóa file logo sau khi xóa record
     */
    public function delete(int $id)
    {
        $brand = $this->brandRepository->findByIdOrFail($id);
        $logoPath = $brand->logo;

        $result = parent::delete($id);

        if ($result && $logoPath) {
            $this->deleteLogo($logoPath);
        }

        return $result;
    }

    /**
     * Upload logo và trả về path
     */
    protected function uploadLogo(UploadedFile $file): string
    {
        $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
        return $file->storeAs('brands', $filename, 'public');
    }

    /**
     * Xóa logo khỏi storage
     */
    protected function deleteLogo(string $path): void
    {
        if (Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }
}
