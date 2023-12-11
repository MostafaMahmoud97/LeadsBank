<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Transaction extends Model
{
    use HasFactory,SoftDeletes;

    protected $fillable = [
        'id',
        'invoice_id',
        'client_id',
        'status',
        'token',
        'correlation',
        'build',
        'paypal_link'
    ];

    protected $hidden = [
        "created_at",
        "updated_at"
    ];

    public function Invoice(){
        return $this->belongsTo(Invoice::class,"invoice_id","id");
    }

}
