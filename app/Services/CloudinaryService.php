<?php

namespace App\Services;

use Cloudinary\Configuration\Configuration;
use Cloudinary\Api\Upload\UploadApi;

class CloudinaryService
{
    public function upload($file, $folder = 'techzone/products')
    {
        if (!$file) return null;

        // Cấu hình trực tiếp từ env để đảm bảo không bị null
        $config = Configuration::instance([
            'cloud' => [
                'cloud_name' => env('CLOUDINARY_CLOUD_NAME'),
                'api_key'    => env('CLOUDINARY_API_KEY'),
                'api_secret' => env('CLOUDINARY_API_SECRET'),
            ],
            'url' => [
                'secure' => true
            ]
        ]);

        $upload = new UploadApi($config);

        $result = $upload->upload(
            $file->getRealPath(),
            ['folder' => $folder]
        );

        return $this->buildStoredPath($result);
    }

    public function buildUrl(?string $imagePath): ?string
    {
        if (!$imagePath) {
            return null;
        }

        $value = trim((string) $imagePath);

        if ($value === '') {
            return null;
        }

        if (str_starts_with($value, 'http://') || str_starts_with($value, 'https://')) {
            return $value;
        }

        if (str_starts_with($value, '/')) {
            return $value;
        }

        return rtrim((string) config('services.cloudinary.base_url'), '/') . '/' . ltrim($value, '/');
    }

    private function buildStoredPath($result): ?string
    {
        $publicId = data_get($result, 'public_id');
        if (!$publicId) {
            return null;
        }

        $format = data_get($result, 'format');
        return $format ? $publicId . '.' . $format : $publicId;
    }
}
