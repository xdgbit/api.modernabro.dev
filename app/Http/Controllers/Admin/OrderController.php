<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\Order\Order;
use App\Models\Product\Product;
use App\Services\Order\OrderStatusService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use InvalidArgumentException;

class OrderController extends Controller
{
    private const ALLOWED_SORT_COLUMNS = [
        'id',
        'code',
        'status',
        'payment_status',
        'subtotal',
        'discount_amount',
        'total_amount',
        'created_at',
    ];

    public function stats()
    {
        $total = Order::count();

        $pending = Order::where('status', 'pending')->count();
        $processing = Order::where('status', 'processing')->count();
        $shipped = Order::where('status', 'shipped')->count();
        $delivered = Order::where('status', 'delivered')->count();
        $cancelled = Order::where('status', 'cancelled')->count();

        $unpaid = Order::where('payment_status', 'unpaid')
            ->whereNotIn('status', ['cancelled', 'returned'])
            ->count();

        // مجموع فروش: سفارش‌های پرداخت‌شده یا بعد از آن، بدون لغو و مرجوعی
        $revenue = (int) Order::query()
            ->whereIn('payment_status', ['paid', 'partially_paid'])
            ->whereNotIn('status', ['cancelled', 'returned'])
            ->sum('total_amount');

        // سفارش‌هایی که نیاز به پیگیری دارند: پرداخت‌نشده یا در انتظار پردازش
        $attentionQuery = Order::query()
            ->where(function ($query) {
                $query->where('payment_status', 'unpaid')
                    ->orWhere('status', 'paid');
            })
            ->whereNotIn('status', ['cancelled', 'returned', 'delivered']);

        $attentionCount = (clone $attentionQuery)->count();

        $attentionOrders = (clone $attentionQuery)
            ->with('userCustomer:id,first_name,last_name')
            ->latest('id')
            ->limit(50)
            ->get()
            ->map(fn(Order $order) => [
                'id'               => $order->id,
                'code'             => $order->code,
                'receiver_name'    => $order->receiver_name,
                'payment_status'   => $order->payment_status,
                'total_amount'     => $order->total_amount,
                'jalali_created_at'=> $order->jalali_created_at ?? null,
            ])
            ->values();

        return ApiResponse::success([
            'total'              => $total,
            'pending'            => $pending,
            'processing'         => $processing,
            'shipped'            => $shipped,
            'delivered'          => $delivered,
            'cancelled'          => $cancelled,
            'unpaid'             => $unpaid,
            'revenue'            => $revenue,
            'attention_count'    => $attentionCount,
            'fa_attention_count' => ConvertNumbersEntoFa($attentionCount),
            'attention_orders'   => $attentionOrders,
        ]);
    }

    public function datatable(Request $request)
    {
        $search = trim((string) $request->input('search', ''));
        $dir = $request->input('dir') === 'asc' ? 'asc' : 'desc';
        $length = min(max((int) $request->input('length', 10), 1), 100);

        $columnName = $request->input('column_name');
        $columnName = in_array($columnName, self::ALLOWED_SORT_COLUMNS, true)
            ? $columnName
            : 'id';

        $query = Order::query()
            ->with('userCustomer:id,first_name,last_name,mobile_number')
            ->withCount('items');

        if ($search !== '')
        {
            $query->where(function ($query) use ($search) {
                $query->where('code', 'like', "%{$search}%")
                    ->orWhere('receiver_name', 'like', "%{$search}%")
                    ->orWhere('receiver_mobile', 'like', "%{$search}%")
                    ->orWhereHas('userCustomer', function ($query) use ($search) {
                        $query->where('first_name', 'like', "%{$search}%")
                            ->orWhere('last_name', 'like', "%{$search}%")
                            ->orWhere('mobile_number', 'like', "%{$search}%");
                    });
            });
        }

        $this->applyFilters($query, $request);

        $orders = $query
            ->orderBy($columnName, $dir)
            ->paginate($length);

        $orders->getCollection()->transform(
            fn(Order $order) => $this->transformRow($order)
        );

        return ApiResponse::success($orders);
    }

    private function applyFilters($query, Request $request): void
    {
        if ($request->filled('code'))
        {
            $query->where('code', 'like', '%' . trim($request->code) . '%');
        }

        if ($request->filled('status'))
        {
            $query->where('status', $request->status);
        }

        if ($request->filled('payment_status'))
        {
            $query->where('payment_status', $request->payment_status);
        }

        if ($request->filled('receiver'))
        {
            $term = trim($request->receiver);

            $query->where(function ($query) use ($term) {
                $query->where('receiver_name', 'like', "%{$term}%")
                    ->orWhere('receiver_mobile', 'like', "%{$term}%");
            });
        }

        if ($request->filled('customer'))
        {
            $term = trim($request->customer);

            $query->whereHas('userCustomer', function ($query) use ($term) {
                $query->where('first_name', 'like', "%{$term}%")
                    ->orWhere('last_name', 'like', "%{$term}%")
                    ->orWhere('mobile_number', 'like', "%{$term}%");
            });
        }

        if ($request->filled('tracking_code'))
        {
            $query->where('tracking_code', 'like', '%' . trim($request->tracking_code) . '%');
        }

        if ($request->filled('created_from'))
        {
            $query->whereDate('created_at', '>=', $request->created_from);
        }

        if ($request->filled('created_to'))
        {
            $query->whereDate('created_at', '<=', $request->created_to);
        }

        if ($request->filled('amount_from'))
        {
            $query->where('total_amount', '>=', (int) $request->amount_from);
        }

        if ($request->filled('amount_to'))
        {
            $query->where('total_amount', '<=', (int) $request->amount_to);
        }
    }

    public function jspanel(Request $request)
    {
        $orderId = $request->input('order_id');

        if (!$orderId)
        {
            return ApiResponse::error('شناسه سفارش الزامی است.', null, 422);
        }

        $order = Order::query()
            ->with([
                'userCustomer:id,first_name,last_name,mobile_number',
                'items',
            ])
            ->find($orderId);

        if (!$order)
        {
            return ApiResponse::error('سفارش موردنظر پیدا نشد.', null, 404);
        }

        return ApiResponse::success([
            'order'            => $this->transformDetail($order),
            'allowed_statuses' => $this->allowedStatuses($order),
        ]);
    }

    public function statusPanel(Request $request)
    {
        $validated = $request->validate([
            'order_id' => ['required', 'integer', 'exists:orders,id'],
        ]);

        $order = Order::query()->findOrFail($validated['order_id']);

        return ApiResponse::success([
            'order'            => [
                'id'            => $order->id,
                'code'          => $order->code,
                'status'        => $order->status,
                'tracking_code' => $order->tracking_code,
            ],
            'allowed_statuses' => $this->allowedStatuses($order),
        ]);
    }

    public function changeStatus(Request $request, Order $order, OrderStatusService $orderStatusService)
    {
        $validated = $request->validate([
            'status'        => ['required', 'string', Rule::in(Order::STATUSES)],
            'tracking_code' => ['nullable', 'string', 'max:100', 'required_if:status,shipped'],
            'admin_note'    => ['nullable', 'string', 'max:2000', 'required_if:status,cancelled', 'required_if:status,returned'],
        ]);

        $expert = auth('user_expert')->user();

        // دسترسی بر اساس نوع تغییر وضعیت
        $permission = match ($validated['status'])
        {
            'cancelled' => 'orders.cancel',
            'returned'  => 'orders.return',
            default     => 'orders.edit',
        };

        abort_unless($expert?->can($permission), 403, 'شما مجوز انجام این عملیات را ندارید.');

        try
        {
            $order = $orderStatusService->change(
                order: $order,
                newStatus: $validated['status'],
                trackingCode: $validated['tracking_code'] ?? null,
                adminNote: $validated['admin_note'] ?? null,
                changedBy: $expert?->id,
            );
        }
        catch (InvalidArgumentException $exception)
        {
            return ApiResponse::error($exception->getMessage(), null, 422);
        }

        return ApiResponse::success($this->transformDetail($order->load(['userCustomer', 'items'])), 'وضعیت سفارش با موفقیت تغییر کرد.');
    }

    public function destroy(Order $order)
    {
        // سفارش‌ها فقط حذف نرم می‌شوند و قبل از حذف باید در وضعیت پایانی باشند
        if (!in_array($order->status, ['cancelled', 'returned'], true))
        {
            return ApiResponse::error(
                'فقط سفارش‌های لغوشده یا مرجوع‌شده قابل حذف هستند.',
                null,
                422
            );
        }

        $order->delete();

        return ApiResponse::success(null, 'سفارش با موفقیت حذف شد.');
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */

    /**
     * وضعیت‌های مجاز بعدی بر اساس وضعیت فعلی سفارش.
     * قوانین انتقال وضعیت فقط اینجا تعریف می‌شوند.
     */
    private function allowedStatuses(Order $order): array
    {
        $transitions = [
            'pending'    => ['paid', 'cancelled'],
            'paid'       => ['processing', 'cancelled'],
            'processing' => ['shipped', 'cancelled'],
            'shipped'    => ['delivered', 'returned'],
            'delivered'  => ['returned'],
            'cancelled'  => [],
            'returned'   => [],
        ];

        return $transitions[$order->status] ?? [];
    }

    private function transformRow(Order $order): array
    {
        $customer = $order->userCustomer;

        return [
            'id'               => $order->id,
            'code'             => $order->code,
            'jalali_created_at'=> $order->jalali_created_at ?? null,
            'customer'         => [
                'id'            => $customer?->id,
                'full_name'     => trim(($customer?->first_name ?? '') . ' ' . ($customer?->last_name ?? '')),
                'mobile_number' => $customer?->mobile_number,
            ],
            'receiver_name'    => $order->receiver_name,
            'items_count'      => $order->items_count,
            'subtotal'         => $order->subtotal,
            'discount_amount'  => $order->discount_amount,
            'total_amount'     => $order->total_amount,
            'payment_status'   => $order->payment_status,
            'status'           => $order->status,
            'status_label'     => Order::STATUS_LABELS[$order->status] ?? $order->status,
        ];
    }

    private function transformDetail(Order $order): array
    {
        $customer = $order->userCustomer;

        return [
            'id'               => $order->id,
            'code'             => $order->code,
            'status'           => $order->status,
            'status_label'     => Order::STATUS_LABELS[$order->status] ?? $order->status,
            'payment_status'   => $order->payment_status,
            'jalali_created_at'=> $order->jalali_created_at ?? null,
            'customer'         => [
                'id'            => $customer?->id,
                'full_name'     => trim(($customer?->first_name ?? '') . ' ' . ($customer?->last_name ?? '')),
                'mobile_number' => $customer?->mobile_number,
            ],
            'receiver_name'    => $order->receiver_name,
            'receiver_mobile'  => $order->receiver_mobile,
            'shipping_address' => $order->shipping_address,
            'postal_code'      => $order->postal_code,
            'tracking_code'    => $order->tracking_code,
            'description'      => $order->description,
            'admin_note'       => $order->admin_note,
            'subtotal'         => $order->subtotal,
            'discount_amount'  => $order->discount_amount,
            'shipping_cost'    => $order->shipping_cost,
            'total_amount'     => $order->total_amount,
            'items'            => $order->items->map(fn($item) => [
                'id'           => $item->id,
                'product_name' => $item->product_name,
                'product_sku'  => $item->product_sku,
                'unit_price'   => $item->unit_price,
                'sale_price'   => $item->sale_price,
                'quantity'     => $item->quantity,
                'total_price'  => $item->total_price,
            ])->values(),
        ];
    }
}