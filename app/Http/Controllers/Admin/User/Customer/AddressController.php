<?php

namespace App\Http\Controllers\Admin\User\Customer;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\Location\City;
use App\Models\Location\Province;
use App\Models\User\Customer\UserCustomer;
use App\Models\User\Customer\UserCustomerAddress;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AddressController extends Controller
{
    /* ======================================================
       List addresses of a customer
    ====================================================== */

    public function index(UserCustomer $customer)
    {
        $addresses = $customer->addresses()
            ->with(['province:id,title', 'city:id,title'])
            ->orderByDesc('is_default')
            ->orderBy('id')
            ->get()
            ->map(fn(UserCustomerAddress $address) => $this->transform($address));

        return ApiResponse::success([
            'addresses' => $addresses,
        ]);
    }

    /* ======================================================
       Create address
    ====================================================== */

    public function store(Request $request, UserCustomer $customer)
    {

        $data = $this->validated($request);

        $address = DB::transaction(function () use ($customer, $data) {

            $hasAddresses = $customer->addresses()->exists();

            // First address of a customer is always the default one
            $isDefault = $data['is_default'] || !$hasAddresses;

            if ($isDefault)
            {
                $customer->addresses()->update(['is_default' => false]);
            }

            return $customer->addresses()->create([
                ...$data,
                'is_default' => $isDefault,
            ]);
        });

        return ApiResponse::success([
            'address' => $this->transform($address->load(['province:id,title', 'city:id,title'])),
        ]);
    }

    /* ======================================================
       Update address
    ====================================================== */

    public function update(Request $request, UserCustomer $customer, UserCustomerAddress $address)
    {
        $this->ensureOwnership($customer, $address);

        $data = $this->validated($request);

        DB::transaction(function () use ($customer, $address, $data) {

            if ($data['is_default'])
            {
                $customer->addresses()
                    ->where('id', '!=', $address->id)
                    ->update(['is_default' => false]);
            }

            // Default flag is changed only through store / setDefault / destroy
            unset($data['is_default']);

            $address->update($data);
        });

        // Keep at least one default address if the edited one was default and
        // the request explicitly removed that flag
        if ($request->boolean('is_default') === false && $address->wasChanged('is_default'))
        {
            $this->ensureDefaultExists($customer);
        }

        return ApiResponse::success([
            'address' => $this->transform($address->fresh(['province:id,title', 'city:id,title'])),
        ]);
    }

    /* ======================================================
       Set default address
    ====================================================== */

    public function setDefault(UserCustomer $customer, UserCustomerAddress $address)
    {
        $this->ensureOwnership($customer, $address);

        DB::transaction(function () use ($customer, $address) {
            $customer->addresses()->update(['is_default' => false]);
            $address->update(['is_default' => true]);
        });

        return ApiResponse::success([
            'address' => $this->transform($address->fresh(['province:id,title', 'city:id,title'])),
        ]);
    }

    /* ======================================================
       Delete address
    ====================================================== */

    public function destroy(UserCustomer $customer, UserCustomerAddress $address)
    {
        $this->ensureOwnership($customer, $address);

        DB::transaction(function () use ($customer, $address) {
            $wasDefault = $address->is_default;

            $address->delete();

            if ($wasDefault)
            {
                $this->ensureDefaultExists($customer);
            }
        });

        return ApiResponse::success([
            'deleted' => true,
        ]);
    }

    /* ======================================================
       Helpers
    ====================================================== */

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'title'           => ['nullable', 'string', 'max:100'],
            'receiver_name'   => ['nullable', 'string', 'max:150'],
            'receiver_mobile' => ['nullable', 'string', 'regex:/^09\d{9}$/'],
            'province_id'     => ['nullable', 'integer', 'exists:provinces,id'],
            'city_id'         => ['nullable', 'integer', 'exists:cities,id'],
            'address'         => ['required', 'string', 'max:1000'],
            'postal_code'     => ['nullable', 'digits:10'],
            'is_default'      => ['sometimes', 'boolean'],
        ], [
            'receiver_mobile.regex' => 'شماره موبایل گیرنده معتبر نیست.',
            'postal_code.digits'    => 'کد پستی باید ۱۰ رقم باشد.',
        ]);

        $data['is_default'] = (bool)($data['is_default'] ?? false);

        // Make sure the city belongs to the selected province
        if (!empty($data['city_id']))
        {
            $cityBelongsToProvince = City::query()
                ->whereKey($data['city_id'])
                ->when(
                    !empty($data['province_id']),
                    fn($q) => $q->where('province_id', $data['province_id'])
                )
                ->exists();

            if (!$cityBelongsToProvince)
            {
                throw ValidationException::withMessages([
                    'city_id' => ['شهر انتخاب‌شده با استان مطابقت ندارد.'],
                ]);
            }
        }

        // Empty strings become null
        foreach (['title', 'receiver_name', 'receiver_mobile', 'postal_code'] as $key)
        {
            if (isset($data[ $key ]) && trim($data[ $key ]) === '')
            {
                $data[ $key ] = null;
            }
        }

        $data['address'] = trim($data['address']);

        return $data;
    }

    private function ensureOwnership(UserCustomer $customer, UserCustomerAddress $address): void
    {
        abort_if($address->customer_id !== $customer->id, 404);
    }

    private function ensureDefaultExists(UserCustomer $customer): void
    {
        if ($customer->addresses()->where('is_default', true)->exists())
        {
            return;
        }

        $latest = $customer->addresses()->latest('id')->first();

        if ($latest)
        {
            $latest->update(['is_default' => true]);
        }
    }

    private function transform(UserCustomerAddress $address): array
    {
        return [
            'id'              => $address->id,
            'title'           => $address->title,
            'receiver_name'   => $address->receiver_name,
            'receiver_mobile' => $address->receiver_mobile,
            'province_id'     => $address->province_id,
            'city_id'         => $address->city_id,
            'province'        => $address->province?->title,
            'city'            => $address->city?->title,
            'address'         => $address->address,
            'postal_code'     => $address->postal_code,
            'is_default'      => (bool)$address->is_default,
        ];
    }
}