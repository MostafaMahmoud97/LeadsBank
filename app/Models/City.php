<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class City extends Model
{
    use HasFactory;

    protected $fillable = [
        "id",
        "name",
        "state_id"
    ];

    protected $hidden = [
        "created_at",
        "updated_at"
    ];

    public function State(){
        return $this->belongsTo(State::class,"state_id","id");
    }
}
