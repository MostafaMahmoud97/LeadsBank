<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CommissionCart extends Model
{
    use HasFactory,SoftDeletes;

    protected $fillable = [
        "id",
        "leads_bank_id",
        "client_id"
    ];

    protected $hidden = [
        "created_at",
        "updated_at",
        "deleted_at"
    ];

    public function LeadBank(){
        return $this->belongsTo(LeadsBank::class,"leads_bank_id","id");
    }
}
