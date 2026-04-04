<?php

namespace App\Repositories;

use App\Models\User;
use App\Repositories\Interfaces\UserRepositoryInterface;
use Illuminate\Support\Facades\Hash;

class UserRepository extends BaseRepository implements UserRepositoryInterface
{
    public function __construct(User $model)
    {
        parent::__construct($model);
    }

    public function getUserByEmail($email)
    {
        return $this->model->where('email', $email)->first();
    }

    public function getAllUsers(int $perPage = 10, ?string $keyword = null)
    {
        $query = $this->model->query();

        if ($keyword) {
            $query->where('name', 'LIKE', "%{$keyword}%")
                ->orWhere('email', 'LIKE', "%{$keyword}%");
        }

        return $query->orderBy('created_at', 'DESC')->orderBy('id', 'DESC')->paginate($perPage);
    }

    public function toggleLock(int $id): bool
    {
        $user = $this->model->findOrFail($id);
        return $user->update(['is_locked' => !$user->is_locked]);
    }

    public function createUser(array $data)
    {
        $data['password'] = Hash::make($data['password']);
        return $this->model->create($data);
    }

    public function resetPassword(int $id, string $password): bool
    {
        $user = $this->model->findOrFail($id);
        return $user->update(['password' => Hash::make($password)]);
    }
}
