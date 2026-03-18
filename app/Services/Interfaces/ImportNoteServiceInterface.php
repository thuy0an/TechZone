<?php

namespace App\Services\Interfaces;

interface ImportNoteServiceInterface extends BaseServiceInterface
{
    public function getImportNotes($request);
    public function getImportNoteDetail(int $id);
    public function createDraft(int $adminId, array $data);
    public function updateDraft(int $id, array $data);
    public function completeNote(int $id);
    public function recordPayment(int $id, float $amount);
}
