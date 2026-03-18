<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Api\BaseApiController;
use App\Http\Requests\Admin\ImportNote\StoreImportNoteRequest;
use App\Services\Interfaces\ImportNoteServiceInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ImportNoteController extends BaseApiController
{
    protected ImportNoteServiceInterface $importNoteService;

    public function __construct(ImportNoteServiceInterface $importNoteService)
    {
        $this->importNoteService = $importNoteService;
    }

    public function index(Request $request)
    {
        try {
            $notes = $this->importNoteService->getImportNotes($request);
            return $this->paginatedResponse($notes, 'Danh sách phiếu nhập kho');
        } catch (\Exception $e) {
            return $this->errorResponse('Lỗi tải danh sách', 500, $e->getMessage());
        }
    }

    public function show($id)
    {
        try {
            $note = $this->importNoteService->getImportNoteDetail($id);
            return $this->successResponse($note, 'Chi tiết phiếu nhập');
        } catch (\Exception $e) {
            return $this->errorResponse('Không tìm thấy phiếu nhập', 404, $e->getMessage());
        }
    }

    public function store(StoreImportNoteRequest $request)
    {
        try {
            $adminId = Auth::id(); // Lấy ID của Admin đang đăng nhập
            $note = $this->importNoteService->createDraft($adminId, $request->validated());
            return $this->createdResponse($note, 'Tạo phiếu nhập Draft thành công');
        } catch (\Exception $e) {
            return $this->errorResponse('Lỗi tạo phiếu nhập', 400, $e->getMessage());
        }
    }

    public function update(StoreImportNoteRequest $request, $id) // Có thể dùng chung Store Request để validate
    {
        try {
            $note = $this->importNoteService->updateDraft($id, $request->validated());
            return $this->successResponse($note, 'Cập nhật phiếu nhập thành công');
        } catch (\Exception $e) {
            return $this->errorResponse('Lỗi cập nhật', 400, $e->getMessage());
        }
    }

    // API đổi trạng thái thành Hoàn thành (Duyệt nhập kho)
    public function complete($id)
    {
        try {
            $note = $this->importNoteService->completeNote($id);
            return $this->successResponse($note, 'Đã hoàn thành phiếu nhập. Tồn kho và Giá bán đã được cập nhật!');
        } catch (\Exception $e) {
            return $this->errorResponse('Lỗi hoàn thành phiếu nhập', 400, $e->getMessage());
        }
    }

    public function pay(Request $request, $id)
    {
        $request->validate([
            'amount' => 'required|numeric|min:1'
        ]);

        try {
            $note = $this->importNoteService->recordPayment($id, $request->input('amount'));
            return $this->successResponse($note, 'Ghi nhận thanh toán thành công!');
        } catch (\Exception $e) {
            return $this->errorResponse('Lỗi thanh toán', 400, $e->getMessage());
        }
    }
}
