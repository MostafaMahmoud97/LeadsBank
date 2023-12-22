<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Casts\Attribute;

class LeadsBank extends Model
{
    use HasFactory,SoftDeletes;

    protected $table = "leads_bank";
    protected $appends = ["price_x"];

    protected $fillable = [
        'lead_id',
        'customer_type',
        'first_name',
        'last_name',
        'phone',
        'is_phone_receives_txt',
        'mobile',
        'is_mobile_receives_txt',
        'email',
        'preferred_language',
        'location',
        'street',
        'unit',
        'country_id',
        'state_id',
        'city_id',
        'is_hoa',
        'is_active',
        'days',
        'price',
        'status',
        'sort_status',
        'source',
        'user_id',
        'county',
        'citizenship_status',
        'home_occupancy',



        //---if company
        'company_name',
        'company_business_model',

        'building_type',
        'time_to_contact',
        'way_to_contact',
        'lead_type',
        'is_decision_maker_present',
        'house_ownership',
        'zip_code',
        'created_at',
        'lng',
        'lat',

        'rate',
        'last_time_communicated',
        'transaction_type',
        'price_percentage',
        'commission_based',
        'commission_type',
        'description',
        'is_archive'
    ];

    protected $hidden = [
        "updated_at",
        "deleted_at"
    ];

    public function ServiceTypes(){
        return $this->hasMany(ServiceType::class,"leads_bank_id","id");
    }

    public function Clients(){
        return $this->hasMany(Client::class,"leads_bank_id","id");
    }



    public function getPriceXAttribute(){
        $coin_price = config("coins_services.coins.coins_price"); // this main 1 coin = 1 $
        return $this->transaction_type == "immediate" ? $this->price_percentage / $coin_price : $this->price_percentage;
    }

}
