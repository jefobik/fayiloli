<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class NotificationController extends Controller
{

    public function fetchNotifications()
    {
        $cacheKey = 'notifications';

        if (Cache::has($cacheKey)) {
            $notifications = Cache::get($cacheKey);
        } else {
            $notifications = Notification::where('dismiss_status', 'PENDING')
                ->where('status', 'UNREAD')
                ->latest()->limit(10)->get();

            Cache::put($cacheKey, $notifications, now()->addMinutes(5));
        }

        session()->put('notifications', $notifications);

        $view = view('notifications.fetch', compact('notifications'))->render();

        return response()->json(['html' => $view, 'count' => count($notifications)]);
    }


    public function dismiss(Request $request, Notification $notification)
    {
        $authUser = $request->authUser;

        if (!isset($authUser['roles'][0]['name']) || !isset($authUser['id'])) {
            return response()->json(['message' => 'Invalid user data'], 400);
        }

        $isCustomer = $authUser['roles'][0]['name'] === 'customer';

        $query = Notification::where($isCustomer ? 'user_id' : 'created_by_id', $authUser['id'])
            ->where('dismiss_status', 'PENDING')
            ->where('status', 'UNREAD')
            ->latest()
            ->take(3);

        try {
            $notification->update([
                'dismiss_status' => 'DISMISSED',
            ]);

            Cache::forget('dashboard_data_' . $authUser['id']);

            $data = $query->get();

            $view = view('notifications.list', compact('data'))->render();

            return response()->json(['html' => $view, 'message' => 'Notification dismissed successfully']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'An error occurred while processing the request'], 500);
        }
    }


    public function store(Request $request)
    {
        $request->validate([
            'customer_id' => 'required',
            'message' => 'required|string',
        ]);

        $notification = Notification::create([
            'user_id'       => $request->customer_id,
            'user_type'     => User::class,
            'activity_type' => 'note_added',
            'model_type'    => User::class,
            'model_id'      => $request->customer_id,
            'message'       => $request->message,
        ]);

        $user  = $notification->user;
        $data  = Notification::where('user_id', $notification->user_id)->latest()->take(10)->get();
        $image = false;

        $view = view('notifications.list', compact('data', 'user', 'image'))->render();

        return response()->json(['html' => $view, 'message' => 'Notification added successfully']);
    }

    public function show(Request $request, Notification $notification)
    {
        $notification->update(['status' => 'READ']);

        return view('notifications.show', compact('notification'));
    }
}
