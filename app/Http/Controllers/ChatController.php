<?php

namespace App\Http\Controllers;

use App\Models\ChatUser;
use App\Models\Chat;
use Hashids\Hashids;

class ChatController extends Controller
{
    /**
     * 用户列表
     *
     * @return mixed
     */
    public function user()
    {
        $uid = $_REQUEST['me'];
        $users = ChatUser::whereIn('uid', function($query) use ($uid) {
            $query->select('from_uid')
                ->from('chats')
                ->where('from_uid', $uid)
                ->orWhere('target_uid', $uid)
                ->orderBy('id', 'desc')
                ->distinct();
        })
            ->simplePaginate(20);
        $users = $users->keyBy('uid');
        $hash = new Hashids('1766', 6, 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ123567890');
        foreach ($users as $u => &$user) {
            $user->hashid = $hash->encode($u);
        }
        $me    = $users[$uid] ?? [];
        unset($users[$uid]);
        return view('chat.user', compact('users', 'me'));
    }

    /**
     * 聊天详情
     *
     * @return mixed
     */
    public function detail()
    {
        $uid     = $_REQUEST['me'];
        $target = $_REQUEST['target'] ?? 0;
        if (empty($target)) {
            return view('error');
        }
        $chats = Chat::where(function($query) use ($uid, $target) {
            $query->where('from_uid', $uid)
                ->where('target_uid', $target);
        })
            ->orWhere(function($query) use ($uid, $target) {
                $query->where('from_uid', $target)
                    ->where('target_uid', $uid);
            })
            ->orderBy('id', 'desc')
            ->paginate(20);
        $users = ChatUser::select('uid', 'name', 'avatar', 'last_operate')
            ->whereIn('uid', [$uid, $target])
            ->get();
        $users = $users->keyBy('uid');
        foreach ($chats as &$chat) {
            $user = $users[$chat->from_uid] ?? [];
            $chat->name   = $user->name ?? '';
            $chat->avatar = $user->avatar ?? '';
            $chat->last_operate = $user->last_operate ?? 0;
        }
        $me = $users[$uid] ?? [];

        return view('chat.detail', compact('chats', 'me'));

    }

    /**
     * 聊天列表
     *
     * @return mixed
     */
    public function list()
    {
        $uid = $_REQUEST['me'];
        $_chats = Chat::where('from_uid', $uid)
            ->orWhere('target_uid', $uid)
            ->paginate(20);
        $uids = [];
        foreach ($_chats as $chat) {
            $uids[$chat->from_uid]   = 1;
            $uids[$chat->target_uid] = 1;
        }
        $users = ChatUser::select('uid', 'name', 'avatar', 'last_operate')
            ->whereIn('uid', array_keys($uids))
            ->get();
        $users = $users->keyBy('uid');
        foreach ($_chats as &$chat) {
            $user = $users[$chat->from_uid] ?? [];
            $chat->name   = $user->name ?? '';
            $chat->avatar = $user->avatar ?? '';
            $chat->last_operate = $user->last_operate ?? 0;
        }
        // 聚合
        $chats = [];
        foreach ($_chats as $chat) {
            if ($chat->from_uid == $uid) {
                $chats[$chat->target_uid][] = $chat;
            } else {
                $chats[$chat->from_uid][] = $chat;
            }
        }
        $me = $users[$uid] ?? [];

        return view('chat.detail', compact('chats', 'me'));
    }
}
