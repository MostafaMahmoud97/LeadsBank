<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Invoice extends Model
{
    use HasFactory,SoftDeletes;

    protected $fillable = [
        'id',
        'client_id',
        'total_price',
        'status'
    ];

    protected $hidden = [
        "created_at",
        "updated_at"
    ];

    public function ImmediateCarts(){
        return $this->hasMany(ImmediateCart::class,"invoice_id","id");
    }

    public function Transaction(){
        return $this->hasOne(Transaction::class,"invoice_id","id");
    }
}
