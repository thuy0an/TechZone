<?php

namespace App\Services\Interfaces;

interface ProductServiceInterface extends BaseServiceInterface
{
    public function getListForStorefront($request);
    public function getDetailForStorefront($id);
}
