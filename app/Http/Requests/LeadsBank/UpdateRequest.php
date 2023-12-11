<?php

namespace App\Http\Requests\LeadsBank;

use App\Rules\NorthAmericanPhoneNumber;
use Illuminate\Foundation\Http\FormRequest;

class UpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'customer_type'          => 'required|in:residential,commercial',
            'first_name'             => 'required|max:20',
            'last_name'              => 'required|max:20',
            'phone'                  => ['nullable', 'numeric', 'digits:10', $this->filled('phone') ? new NorthAmericanPhoneNumber() : null],
            'is_phone_receives_txt'  => 'boolean',
            'mobile'                 => ['required', 'numeric', 'digits:10', 'unique:leads_bank,id,'.$this->lead_id, new NorthAmericanPhoneNumber()],
            'is_mobile_receives_txt' => 'boolean',
            'email'                  => 'required|email',
            'preferred_language'     => 'required',
            'country_id'             => 'required|exists:countries,id',
            'state_id'               => 'required|exists:states,id',
            'alter_state'            => 'sometimes|nullable|exists:states,id',
            'building_type'          => 'required|in:single_family,trailer,town_home',
            'street'                 => 'required',
            'citizenship_status'     => 'nullable|in:US_CITIZEN,LAWFUL_PERMANENT_RESIDENT_ALIEN,OTHER',
            'source'                 => 'required|in:manual,call_center,apn',


            'is_active'              => 'required|boolean',
            'is_hoa'                 => 'required|boolean',


            //--manuvar
            'time_to_contact'        => 'required',
            'way_to_contact'         => 'required',

            'is_decision_maker_present' => 'required',
            'house_ownership'           => 'required',
            'zip_code'                  => 'required',
            'location'                  => 'required|string',
            'county'                    => 'nullable|string',
            'building_coordinates.lat'  => ['sometimes', 'regex:/^[-]?(([0-8]?[0-9])\.(\d+))|(90(\.0+)?)$/'],
            'building_coordinates.lng'  => ['sometimes', 'regex:/^[-]?((((1[0-7][0-9])|([0-9]?[0-9]))\.(\d+))|180(\.0+)?)$/'],

            //--details
            'rate'                      => "required|numeric|between:1,5",
            'last_time_you_communicated'=> "required|date|date_format:Y-m-d H:i:s",
            'transaction_type'          => "required|in:commission based,immediate",
            'price_percentage'          => "required|numeric",
            'commission_based'          => 'required_if:transaction_type,commission based|in:flat rate,split earning,percentage of sale|nullable',
            'commission_type'           => 'required_if:transaction_type,commission based|in:shared,exclusive|nullable'
        ];
    }
}
