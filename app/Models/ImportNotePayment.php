<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ImportNotePayment extends BaseModel
{
    protected $fillable = ['import_note_id', 'admin_id', 'amount'];

    public function importNote()
    {
        return $this->belongsTo(ImportNote::class);
    }

    public function admin()
    {
        return $this->belongsTo(Admin::class); 
    }
}
