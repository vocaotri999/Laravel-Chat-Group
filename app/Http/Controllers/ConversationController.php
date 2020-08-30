<?php

namespace App\Http\Controllers;

use App\Conversation;
use App\Events\NewMessage;
use Illuminate\Http\Request;

class ConversationController extends Controller
{
    public function store()
    {
        $conversation = Conversation::create([
            'message' => request('message'),
            'group_id' => request('group_id'),
            'user_id' => auth()->user()->id,
        ]);

        broadcast(new NewMessage($conversation->load('user')))->toOthers();

        return $conversation->load('user');
    }

    public function index()
    {
        if (!empty(request('group_id')))
            return Conversation::with('user')->with('group')->where('group_id', request('group_id'))->get();
        return Conversation::with('user')->with('group')->get();
    }
}
