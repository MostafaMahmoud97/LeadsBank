<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ImmediateCart extends Model
{
    use HasFactory,SoftDeletes;

    protected $fillable = [
        "id",
        "leads_bank_id",
        "invoice_id",
        "client_id",
        "description",
        "quantity"
    ];

    protected $hidden = [
        "created_at",
        "updated_at",
        "deleted_at"
    ];

    public function Invoice(){
        return $this->belongsTo(Invoice::class,"invoice_id","id");
    }

    public function LeadBank(){
        return $this->belongsTo(LeadsBank::class,"leads_bank_id","id");
    }
}
