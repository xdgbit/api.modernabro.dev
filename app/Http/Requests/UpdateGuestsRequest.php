<?php

namespace App\Http\Requests;

use App\Rules\NationalCode;
use Illuminate\Foundation\Http\FormRequest;
use App\Rules\UserGuest\UniqueGuestMobileNumber;
use App\Rules\UserGuest\UniqueGuestNationalCode;

class UpdateGuestsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $guest = $this->route('guest');
        return [
            'room_id' => [
                'required',
                'integer',
                'exists:reservation_rooms,id',
            ],

            'guests' => [
                'required',
                'array',
            ],

            'guests.*.first_name' => [
                'required',
                'string',
                'max:100',
            ],

            'guests.*.last_name'     => [
                'required',
                'string',
                'max:100',
            ],
            'guests.*.birth_date'    => [
                'required',
                'date_format:Y-m-d',
            ],
            'guests.*.gender'    => [
                'required',

            ],
            'guests.*.national_code' => [
                'required',
                'numeric',
                'distinct',
                new NationalCode('کد ملی نامعتبر است.'),
            ],

            'guests.*.mobile_number' => [
                'required',
                'string',
                'regex:/^09[0-9]{9}$/',
                'distinct',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'room_id.required' => 'انتخاب اتاق الزامی است.',
            'room_id.integer'  => 'شناسه اتاق باید عددی باشد.',
            'room_id.exists'   => 'اتاق انتخاب‌شده معتبر نیست.',

            'guests.required' => 'وارد کردن مهمانان الزامی است.',
            'guests.array'    => 'فرمت مهمانان صحیح نیست.',

            'guests.*.id.integer' => 'شناسه مهمان باید عددی باشد.',
            'guests.*.id.exists'  => 'مهمان انتخاب‌شده معتبر نیست.',

            'guests.*.first_name.required' => 'نام مهمان الزامی است.',
            'guests.*.first_name.string'   => 'نام مهمان باید متنی باشد.',
            'guests.*.first_name.max'      => 'نام مهمان نمی‌تواند بیشتر از ۱۰۰ کاراکتر باشد.',

            'guests.*.last_name.required' => 'نام خانوادگی مهمان الزامی است.',
            'guests.*.last_name.string'   => 'نام خانوادگی مهمان باید متنی باشد.',
            'guests.*.last_name.max'      => 'نام خانوادگی مهمان نمی‌تواند بیشتر از ۱۰۰ کاراکتر باشد.',

            'guests.*.national_code.required' =>
                'کد ملی مهمان الزامی است.',
            'guests.*.national_code.numeric'  =>
                'کد ملی باید عددی باشد.',
            'guests.*.national_code.distinct' =>
                'کد ملی مهمانان نباید تکراری باشد.',

            'guests.*.mobile_number.required' =>
                'شماره موبایل مهمان الزامی است.',
            'guests.*.mobile_number.string'   =>
                'شماره موبایل باید به صورت متن باشد.',
            'guests.*.mobile_number.regex'    =>
                'شماره موبایل باید با 09 شروع شده و ۱۱ رقم باشد.',
            'guests.*.mobile_number.distinct' =>
                'شماره موبایل مهمانان نباید تکراری باشد.',

            'guests.*.birth_date.required' =>
                'تاریخ تولد مهمان الزامی است.',

            'guests.*.birth_date.date_format' =>
                'تاریخ تولد باید با فرمت YYYY-MM-DD باشد.',
        ];
    }
}