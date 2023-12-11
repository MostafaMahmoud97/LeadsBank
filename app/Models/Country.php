<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Country extends Model
{
    use HasFactory;

    protected $fillable = [
        "id",
        "sort_name",
        "name",
        "phone_code"
    ];

    protected $hidden = [
        "created_at",
        "updated_at"
    ];

    public function  States(){
        return $this->hasMany(State::class,"country_id","id");
    }
}
