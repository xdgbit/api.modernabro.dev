<?php

namespace App\Http\Requests\Admin\Accommodation;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreAccommodationPolicyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'accommodation_id' => [
                'required',
                'integer',
                'exists:accommodations,id',
            ],

            'payment_policy' => [
                'required',
                Rule::in(['full', 'partial']),
            ],

            'deposit_percentage' => [
                'required_if:payment_policy,partial',
                'nullable',
                'integer',
                'min:1',
                'max:99',
            ],

            'booking_type_policy' => [
                'required',
                Rule::in(['guaranteed', 'precheck']),
            ],

            'guest_info_policy' => [
                'required',
                Rule::in([
                    'not_required',
                    'all_after_payment',
                    'all_before_payment',
                    'leader_after_payment',
                    'leader_before_payment',
                    'room_leaders_after_payment',
                    'room_leaders_before_payment',
                ]),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'accommodation_id.required' => 'انتخاب اقامتگاه الزامی است.',
            'accommodation_id.exists'   => 'اقامتگاه انتخاب‌شده معتبر نیست.',

            'payment_policy.required' => 'انتخاب سیاست پرداخت الزامی است.',
            'payment_policy.in'       => 'سیاست پرداخت انتخاب‌شده معتبر نیست.',

            'deposit_percentage.required_if' => 'درصد بیعانه الزامی است.',
            'deposit_percentage.integer'     => 'درصد بیعانه باید عدد صحیح باشد.',
            'deposit_percentage.min'         => 'درصد بیعانه حداقل باید ۱ باشد.',
            'deposit_percentage.max'         => 'درصد بیعانه حداکثر می‌تواند ۹۹ باشد.',

            'booking_type_policy.required' => 'انتخاب نوع رزرو الزامی است.',
            'booking_type_policy.in'       => 'نوع رزرو معتبر نیست.',

            'guest_info_policy.required' => 'انتخاب سیاست اطلاعات مهمان الزامی است.',
            'guest_info_policy.in'       => 'سیاست اطلاعات مهمان معتبر نیست.',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        $errors = $validator->errors();

        $res = [
            'success'   => false,
            'errors'    => validation_error_to_api_json($errors),
            'msg_type'  => 'error',
            'msg_title' => 'لطفا مورد زیر را بررسی نمایید:',
            'msg_text'  => $errors->first(),
        ];

        throw new HttpResponseException(
            response()
                ->json($res, 200)
                ->withHeaders([
                    'Content-Type' => 'text/plain',
                    'charset'      => 'utf-8',
                ])
        );

    }

    protected function prepareForValidation(): void
    {
        if ($this->input('payment_policy') === 'full')
        {
            $this->merge([
                'deposit_percentage' => null,
            ]);
        }
    }
}
