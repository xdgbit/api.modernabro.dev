<?php

namespace App\Http\Controllers\Admin;

use App\Models\ContactMessage;
use App\Models\SystemConfig;
use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ContactMessageController extends Controller
{
    public function get_data(): \Illuminate\Http\JsonResponse
    {
        $total = ContactMessage::count();
        $unread = ContactMessage::where('is_read', false)->count();
        $read = ContactMessage::where('is_read', true)->count();
        $today = ContactMessage::whereDate('created_at', today())->count();

        return ApiResponse::success([
            'total'  => ConvertNumbersEntoFa($total),
            'unread' => ConvertNumbersEntoFa($unread),
            'read'   => ConvertNumbersEntoFa($read),
            'today'  => ConvertNumbersEntoFa($today),
        ]);
    }

    public function datatable(Request $request)
    {
        $search = $request->input('search');
        $dir = $request->input('dir', 'desc');
        $length = (int)$request->input('length', 10);
        $columnName = $request->input('column_name', 'id');

        $query = ContactMessage::query();

        if ($search)
        {
            $query->where(function ($query) use ($search) {
                $query
                    ->where('full_name', 'like', "%{$search}%")
                    ->orWhere('mobile', 'like', "%{$search}%")
                    ->orWhere('subject', 'like', "%{$search}%")
                    ->orWhere('message', 'like', "%{$search}%");
            });
        }

        $allowedColumns = [
            'id',
            'full_name',
            'mobile',
            'subject',
            'is_read',
            'created_at',
        ];

        if (!in_array($columnName, $allowedColumns, true))
        {
            $columnName = 'id';
        }

        $dir = $dir === 'asc' ? 'asc' : 'desc';

        $query->orderBy($columnName, $dir);

        $messages = $query->paginate($length);

        $messages->getCollection()
            ->transform(function ($message) {
                return [
                    'id'                  => $message->id,
                    'full_name'           => $message->full_name,
                    'mobile'              => $message->mobile,
                    'subject'             => $message->subject,
                    'is_read'             => (bool)$message->is_read,
                    'jalali_submitted_at' => $message->jalali_submitted_at
                ];
            });

        return ApiResponse::success($messages);
    }
    public function jspanel(Request $request): \Illuminate\Http\JsonResponse
    {
        $request->validate([
            'contact_message_id' => ['required', 'integer', 'exists:contact_messages,id'],
        ]);

        $message = ContactMessage::findOrFail($request->contact_message_id);

        // با باز شدن پنل، پیام خوانده می‌شود
        if (!$message->is_read)
        {
            $message->update(['is_read' => true]);
        }

        return ApiResponse::success([
            'contact_message' => [
                'id'                  => $message->id,
                'full_name'           => $message->full_name,
                'mobile'              => $message->mobile,
                'subject'             => $message->subject,
                'message'             => $message->message,
                'is_read'             => (bool)$message->is_read,
                'jalali_submitted_at' => $message->jalali_submitted_at, // یا هلپر تاریخ شمسی خودتان
            ],
        ]);
    }

    public function toggleRead(ContactMessage $contactMessage): \Illuminate\Http\JsonResponse
    {
        $contactMessage->update(['is_read' => !$contactMessage->is_read]);

        return ApiResponse::success(
            ['is_read' => (bool)$contactMessage->is_read],
            'وضعیت پیام تغییر کرد.'
        );
    }

    public function destroy(ContactMessage $contactMessage): \Illuminate\Http\JsonResponse
    {
        $contactMessage->delete();

        return ApiResponse::success([], 'پیام با موفقیت حذف شد.');
    }
}
