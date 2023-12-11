<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceType extends Model
{
    use HasFactory;

    protected $fillable = [
        "id",
        "leads_bank_id",
        "service_id",
        "title"
    ];

    protected $hidden = [
        "created_at",
        "updated_at"
    ];
}
