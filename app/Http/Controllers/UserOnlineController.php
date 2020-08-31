<?php

namespace App\Http\Controllers;

use App\Events\UserOffline;
use App\Events\UserOnline;
use App\User;
use Illuminate\Http\Request;

class UserOnlineController extends Controller
{
    public function online($id)
    {
        $user = User::find($id);
        $user->status = '1';
        $user->last_online = date('Y-m-d H:i:s');
        $user->save();
        broadcast(new UserOnline($user));
        return $user;
    }

    public function offline($id)
    {
        $user = User::find($id);
        $user->status = '0';
        $user->save();
        broadcast(new UserOffline($user));
        return $user;
    }
}
