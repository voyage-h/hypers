<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Traits\ChatTrait;
use App\Http\Controllers\Traits\UserTrait;
use App\Models\ChatUser;
use App\Models\Chat;
use App\Models\Location;
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
                $last_operate = Carbon::parse($user->last_operate);
                $user->age = $user->birthday ? Carbon::parse($user->birthday)->age : 0;
                $user->is_online    = (time() - $user->last_operate) <= 600;
                $user->last_operate = $last_operate->diffForHumans();
				$user->name = mb_substr($user->name, 0, 5);
                return $user;
            });
        return view('chat.index', compact('users'));
    }

    /**
     * 用户主页
     */
    public function user(int $uid)
    {
        // 获取实时数据
        Cache::remember("update:user:$uid", 600, function() use ($uid) {
            $user = $this->retrieveUser($uid);
            if (! empty($user)) {
                // 更新用户
                $location = $this->parasLocation($user);
                if (! empty($location)) {
                    Location::insertOrIgnore($location);
                }
                // 更新用户
                unset($user['latitude'], $user['longitude'], $user['dev_id']);
                ChatUser::where('uid', $uid)->update($user);
            }
            return 1;
        });

        // 获取聊天列表
        //$users = $this->getUserList($uid);
		$users = [];

        // 查询用户
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
		$me->last_operate = Carbon::parse($me->last_operate)->diffForHumans();
        $me->description = str_replace("\n", '<br/>', $me->description);
		if (Cache::has("refresh:{$uid}")) {
            $start = Cache::get("refresh:{$uid}");
		    $start = Carbon::parse(intval($start))->diffForHumans();
		} else {
		    $start = '';
		}
		return view('chat.user', compact('users', 'me', 'start'));
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
        $users = ChatUser::select('uid', 'name', 'avatar', 'last_operate', 'height', 'weight', 'role', 'birthday')
            ->with('note')
            ->whereIn('uid', [$uid, $target])
            ->get();
        $users = $users->keyBy('uid');
        foreach ($chats as $chat) {
            $user = $users[$chat->from_uid] ?? [];
            $chat->name   = $user->name ?? '';
            $chat->avatar = $user->avatar ?? '';
            $chat->last_operate = $user->last_operate ?? 0;
        }
        $me = $users[$uid] ?? [];
        $target = $users[$target] ?? [];
		$target->age = Carbon::parse($target->birthday)->age;

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

    /**
     * 获取图片
     *
     * @param int $uid
     */
    public function all(int $uid)
    {
        $raw_chats = Chat::where('contents', 'like', 'http%')
            ->where(function($query) use ($uid) {
                $query->where('from_uid', $uid)
                    ->orWhere('target_uid', $uid);
            })
            ->orderBy('created_at', 'desc')
            ->simplePaginate(60);

        $uids = [];
        foreach ($raw_chats as $chat) {
            $uids[$chat->from_uid]   = $chat->from_uid;
            $uids[$chat->target_uid] = $chat->target_uid;
        }
        $users = ChatUser::select('uid', 'name', 'avatar', 'last_operate', 'height', 'weight', 'role')
            ->with('note')
            ->whereIn('uid', array_keys($uids))
            ->get();

        $users = $users->keyBy('uid');
        $chats = [];
        foreach ($raw_chats as $chat) {
            $user = $users[$chat->from_uid] ?? [];
            $chat->name   = $user->name ?? '';
            $chat->avatar = $user->avatar ?? '';
            $chat->last_operate = $user->last_operate ?? 0;
            $chats[$chat->from_uid][] = $chat;
        }
        $me = $users[$uid] ?? [];
        return view('chat.all', compact('chats', 'users', 'me'));
    }
}
