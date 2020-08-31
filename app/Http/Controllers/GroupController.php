<?php

namespace App\Http\Controllers;

use App\Events\GroupCreated;
use App\Group;
use Illuminate\Http\Request;

class GroupController extends Controller
{
    public function store()
    {
        $group = Group::create(['name' => request('name')]);

        $users = collect(request('users'));
        $users->push(auth()->user()->id);
        $fill = [
            $users[0]['id'],
            $users[1]
        ];
        $group->users()->attach($fill);
        broadcast(new GroupCreated($group))->toOthers();

        return $group;
    }
}
