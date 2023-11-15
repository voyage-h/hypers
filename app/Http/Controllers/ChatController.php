<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Traits\ChatTrait;
use App\Http\Controllers\Traits\UserTrait;
use App\Models\ChatUser;
use App\Models\Chat;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

class ChatController extends Controller
{
    use ChatTrait, UserTrait;

    /**
     * 首页
     *
     */
    public function index()
    {
        $users = ChatUser::where('is_suspect', 1)
            ->with('note', 'location')
            ->orderByDesc('last_operate')
            ->get()
            ->map(function ($user) {
                $user->age = $user->birthday ? Carbon::parse($user->birthday)->age : 0;
                return $user;
            });
        return view('chat.index', compact('users'));
    }

    /**
     * 用户聊天列表
     */
    public function user(int $uid)
    {
        $users = $this->getUserList($uid);
        $me = ChatUser::where('uid', $uid)
            ->with(['device' => function ($query) use ($uid) {
                $query->with(['others' => function ($q) use ($uid) {
                    $q->where('uid', '!=', $uid)
					->where('dev_id', '!=', '9478ae2765432087c59c5df2154741cc31c3e33e8f302f9ac2cb7e9ed30724c6')
					->with('user')->limit(6);
                }]);
            }])
            ->first();
        $me->hashid = $this->encodeUid($uid);
        $me->age    = Carbon::parse($me->birthday)->age;
        return view('chat.user', compact('users', 'me'));
    }

    /**
     * 聊天详情
     *
     * @return mixed
     */
    public function detail(int $uid, int $target)
    {
        $chats = Chat::where(function($query) use ($uid, $target) {
            $query->where('from_uid', $uid)
                ->where('target_uid', $target);
        })
            ->orWhere(function($query) use ($uid, $target) {
                $query->where('from_uid', $target)
                    ->where('target_uid', $uid);
            })
            ->orderBy('created_at', 'asc')
            ->get();
        $users = ChatUser::select('uid', 'name', 'avatar', 'last_operate', 'height', 'weight', 'role')
            ->with('note')
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
        $target = $users[$target] ?? [];

        return view('chat.detail', compact('chats', 'me', 'target'));
    }

    /**
     * 更新首页
     *
     */
    public function refresh()
    {
        $users = ChatUser::where('is_suspect', 1)->get();
        $uids = array_column($users->toArray(), 'uid');
        $new_users = $this->retrieveUsers($uids);
        foreach ($new_users as $k => $new_user) {
            $this->updateLocation($new_user);
        }
        foreach ($users as $user) {
            $last_operate = $new_users[$user->uid]['last_operate'] ?? 0;
            $name = $new_users[$user->uid]['name'] ?? '';
            $avatar = $new_users[$user->uid]['avatar'] ?? '';
            $description = $new_users[$user->uid]['description'] ?? '';
            $role = $new_users[$user->uid]['role'] ?? '';
            $weight = $new_users[$user->uid]['weight'] ?? '';
            $height = $new_users[$user->uid]['height'] ?? '';
			$birthday = $new_users[$user->uid]['birthday'] ?? 0;

            if ($last_operate > $user->last_operate) {
                $user->last_operate = $last_operate;
            }
            if ($name && $name != $user->name) {
                $user->name = $name;
            }
            if ($avatar && $avatar != $user->avatar) {
                $user->avatar = $avatar;
            }
            if ($description && $description != $user->description) {
                $user->description = $description;
            }
            if ($role && $role != $user->role) {
                $user->role = $role;
            }
            if ($weight && $weight != $user->weight) {
                $user->weight = $weight;
            }
            if ($height && $height != $user->height) {
                $user->height = $height;
            }
			if ($birthday && $birthday > $user->birthday) {
			    $user->birthday = $birthday;
			}
            $user->save();
        }
        return redirect('/');
    }
}
