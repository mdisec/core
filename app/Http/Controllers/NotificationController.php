<?php

namespace App\Http\Controllers;

use App\Notification;

class NotificationController extends Controller
{
    public function check(){
        $notifications = Notification::where('user_id',\Auth::id())->get();
        return [
            "notifications" => $notifications
        ];
    }
}
