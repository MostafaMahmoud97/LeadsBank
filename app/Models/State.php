<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class State extends Model
{
    use HasFactory;

    protected $fillable = [
        "id",
        "name",
        "country_id",
        "license_requirement",
        "url",
        "phone",
        "address",
        "abbr"
    ];

    protected $hidden = [
        "created_at",
        "updated_at"
    ];

    public function Country(){
        return $this->belongsTo(Country::class,"country_id","id");
    }

    public function Cities(){
        return $this->hasMany(City::class,"state_id","id");
    }
}
