<?php

namespace App\Http\Requests\Admin\Accommodation;

use App\Models\Accommodation\Accommodation;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreAccommodationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'checkin_time' => $this->input('checkin_time')
                ? substr($this->input('checkin_time'), 0, 5)
                : null,

            'checkout_time' => $this->input('checkout_time')
                ? substr($this->input('checkout_time'), 0, 5)
                : null,
        ]);
    }

    public function rules(): array
    {
        return [
            'accommodation_id' => [
                'nullable',
                'integer',
                'exists:accommodations,id',
            ],

            'city_id' => [
                'required',
                'integer',
                'exists:cities,id',
            ],

            'amenity_ids' => [
                'nullable',
                'array',
            ],

            'amenity_ids.*' => [
                'integer',
                'exists:amenities,id',
            ],

            'title' => [
                'required',
                'string',
                'max:255',
            ],

            'type' => [
                'required',
                'in:hotel,villa,suite,cottage',
            ],
            'star_rating' => ['nullable', 'integer', 'min:0', 'max:5'],
            'description' => [
                'nullable',
                'string',
            ],

            'address' => [
                'nullable',
                'string',
            ],

            'phone' => [
                'nullable',
                'string',
            ],
            'host_mobile' => [
                'nullable',
                'string',
            ],

            'is_active' => [
                'sometimes',
                'boolean',
            ],

            'floor_count' => [
                'required',
                'integer',
                'min:0',
            ],

            'checkin_time' => [
                'nullable',
                'date_format:H:i',
            ],

            'checkout_time' => [
                'nullable',
                'date_format:H:i',
            ],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {

            // هنگام ایجاد، اقامتگاه همیشه غیرفعال ذخیره می‌شود.
            if (!$this->filled('accommodation_id'))
            {
                return;
            }

            // اگر درخواست فعال‌سازی نشده، نیازی به بررسی نیست.
            if (!$this->boolean('is_active'))
            {
                return;
            }

            $accommodation = Accommodation::find(
                $this->input('accommodation_id')
            );

            if (!$accommodation)
            {
                return;
            }

            if (!$accommodation->policy()->exists())
            {
                $validator->errors()->add(
                    'is_active',
                    'برای فعال‌سازی اقامتگاه، ابتدا سیاست‌های آن را ذخیره کنید.'
                );
            }

            if (!$accommodation->units()->exists())
            {
                $validator->errors()->add(
                    'is_active',
                    'برای فعال‌سازی اقامتگاه، ابتدا حداقل یک واحد ثبت کنید.'
                );
            }
        });
    }

    public function messages(): array
    {
        return [
            'accommodation_id.integer' => 'شناسه اقامتگاه نامعتبر است.',
            'accommodation_id.exists'  => 'اقامتگاه موردنظر یافت نشد.',

            'city_id.required' => 'انتخاب شهر الزامی است.',
            'city_id.exists'   => 'شهر انتخاب‌شده معتبر نیست.',

            'title.required' => 'وارد کردن عنوان اقامتگاه الزامی است.',
            'title.max'      => 'عنوان اقامتگاه نمی‌تواند بیشتر از ۲۵۵ کاراکتر باشد.',

            'type.required' => 'انتخاب نوع اقامتگاه الزامی است.',
            'type.in'       => 'نوع اقامتگاه نامعتبر است.',

            'floor_count.required' => 'وارد کردن تعداد طبقات الزامی است.',
            'floor_count.integer'  => 'تعداد طبقات باید عدد صحیح باشد.',
            'floor_count.min'      => 'تعداد طبقات نمی‌تواند منفی باشد.',

            'checkin_time.date_format'  => 'فرمت ساعت ورود نامعتبر است.',
            'checkout_time.date_format' => 'فرمت ساعت خروج نامعتبر است.',

            'amenity_ids.array'    => 'امکانات باید به صورت آرایه ارسال شوند.',
            'amenity_ids.*.exists' => 'یکی از امکانات انتخاب‌شده معتبر نیست.',

            'is_active.boolean' => 'وضعیت فعال بودن نامعتبر است.',
        ];
    }

    public function attributes(): array
    {
        return [
            'title'         => 'عنوان اقامتگاه',
            'city_id'       => 'شهر',
            'type'          => 'نوع اقامتگاه',
            'description'   => 'توضیحات',
            'address'       => 'آدرس',
            'phone'         => 'شماره تماس',
            'floor_count'   => 'تعداد طبقات',
            'checkin_time'  => 'ساعت ورود',
            'checkout_time' => 'ساعت خروج',
            'amenity_ids'   => 'امکانات',
            'is_active'     => 'وضعیت فعال بودن',
        ];
    }
}
