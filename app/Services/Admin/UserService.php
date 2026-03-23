<?php

namespace App\Services\Admin;

use App\Repositories\Interfaces\UserRepositoryInterface;
use App\Services\Interfaces\UserServiceInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserService implements UserServiceInterface
{

    protected $userRepo;

    public function __construct(UserRepositoryInterface $userRepo)
    {
        $this->userRepo = $userRepo;
    }

    public function getListUsers(Request $request)
    {

        $perPage = $request->get('limit', 10);
        $keyword = $request->get('keyword');

        return $this->userRepo->getAllUsers($perPage, $keyword);
    }

    public function createNewUser(array $data)
    {
        if (empty($data['password'])) {
            $data['password'] = 'TechZone@2026';
        }

        return $this->userRepo->createUser($data);
    }

    public function toggleUserLock(int $id)
    {
        return $this->userRepo->toggleLock($id);
    }
}
