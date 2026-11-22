<?php

namespace App\Http\Requests\Frontend\Reservation;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class AddRoomRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'accommodation' => [
                'required',
                'string',
            ],

            'unit_id' => [
                'required',
                'integer',
                'exists:accommodation_units,id',
            ],

            'checkin' => [
                'required',
//                'date',
            ],

            'checkout' => [
                'required',
//                'date',
                'after:checkin',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'accommodation.required' => 'لطفاً اقامتگاه را انتخاب کنید.',

            'unit_id.required' => 'لطفاً اتاق را انتخاب کنید.',
            'unit_id.integer'  => 'شناسه اتاق نامعتبر است.',
            'unit_id.exists'   => 'اتاق انتخاب‌شده وجود ندارد.',

            'checkin.required' => 'لطفاً تاریخ ورود را انتخاب کنید.',
            'checkin.date'     => 'تاریخ ورود نامعتبر است.',

            'checkout.required' => 'لطفاً تاریخ خروج را انتخاب کنید.',
            'checkout.date'     => 'تاریخ خروج نامعتبر است.',
            'checkout.after'    => 'تاریخ خروج باید بعد از تاریخ ورود باشد.',
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(
            response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ], 422)
        );
    }
}
