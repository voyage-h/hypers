<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Traits\ChatTrait;
use App\Http\Controllers\Traits\UserTrait;
use App\Models\Chat;
use App\Models\ChatUser;
use App\Models\Location;
use App\Models\UserDevice;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;

class ApiChatController extends Controller
{
    use ChatTrait, UserTrait;

    /**
     * 用户聊天列表
     *
     */
    public function user(int $uid): JsonResponse
    {
        $users = $this->getUserList($uid);

        return response()->json(compact('users'));
    }

    /**
     * 修改备注
     *
     * @param int    $uid
     * @param string $note
     *
     * @return JsonResponse
     */
    public function note(int $uid, string $note = ''): JsonResponse
    {
        $user = ChatUser::where('uid', $uid)->first();
        if ($user) {
            $user->note = $note;
            $user->save();
        }
        return response()->json(['code' => 200, 'msg' => 'ok']);
    }

    /**
     * 关注/取消关注
     *
     * @param int $uid
     *
     * @return JsonResponse
     */
    public function follow(int $uid): JsonResponse
    {
        $user = ChatUser::where('uid', $uid)->first();
        if ($user) {
            $user->is_suspect = $user->is_suspect == 1 ? 0 : 1;
            $user->save();
        }
        return response()->json(['code' => 200, 'msg' => 'ok']);
    }

    /**
     * 更新用户信息
     *
     * @param int $uid
     *
     * @return JsonResponse
     */
    public function refreshUser(int $uid): JsonResponse
    {
        // 获取多账号信息
        $other_uids = Cache::remember("fetch:others:$uid", 3400 * 24, function() use ($uid) {
            return $this->getOtherUids($uid);
        });
        $all_uids  = array_merge([$uid], array_keys($other_uids));
        $all_users = $this->retrieveUsers($all_uids);

        foreach ($all_users as $user_id => $user) {
			$dev_id = $user['dev_id'];

            // 更新设备
			if ($dev_id) {
                UserDevice::insertOrIgnore([
                    'uid'        => $user_id,
                    'dev_id'     => $dev_id,
                    'created_at' => time(),
                ]);
			}

            // 更新位置
            $location = $this->parasLocation($user);
            if (! empty($location)) {
                Location::insertOrIgnore($location);
            }

            // 更新用户
            unset($user['latitude']);
            unset($user['longitude']);
            unset($user['dev_id']);
            $user = ChatUser::where('uid', $user_id)->first();
            if ($user) {
                $user->last_operate = $user['last_operate'];
                $user->description  = $user['description'];
                $user->birthday     = $user['birthday'];
                $user->save();
            } else {
                ChatUser::insertOrIgnore($user);
            }
        }

        $me = $all_users[$uid] ?? [];
        unset($all_users[$uid]);
        return response()->json([
            'me'     => $me,
            'others' => $all_users,
        ]);
    }

    /**
     * 更新聊天信息
     *
     * @param int $uid
     *
     * @return JsonResponse
     */
    public function refreshChats(int $uid): JsonResponse
    {
        $start = Cache::get("refresh:{$uid}", strtotime('-15 day'));
        $start = max($start, strtotime('-15 day'));
        Cache::set("refresh:{$uid}", time());
        $start = date('Y-m-d H:i', $start) . ':00';
        $end   = date('Y-m-d', strtotime('+1 day'));
        $raw_data = $this->retrieveChats($uid, $start, $end);
        if (! empty($raw_data)) {
            // 插入数据库
            $new_uids = [];
            foreach ($raw_data as $value) {
                $new_uids[$value->from_uid] = 1;
                $new_uids[$value->target]   = 1;
                $insert_datas[] = [
                    'from_uid' => $value->from_uid,
                    'target_uid'   => $value->target,
                    'contents' => $value->contents,
                    'S'        => $value->S,
                    'created_at'     => $value->time,
                ];
                if (count($insert_datas) >= 1000) {
                    DB::table('chats')->insertOrIgnore($insert_datas);
                    $insert_datas = [];
                }
            }
            if (! empty($insert_datas)) {
                DB::table('chats')->insertOrIgnore($insert_datas);
            }
            $new_uids = array_keys($new_uids);
            $new_users = $this->retrieveUsers($new_uids);
            foreach ($new_users as $k => $new_user) {
                unset($new_users[$k]['latitude']);
                unset($new_users[$k]['longitude']);
                unset($new_users[$k]['dev_id']);
            }
            DB::table('chat_users')->insertOrIgnore(array_values($new_users));

            // 更新我的信息
            $me_info = $new_users[$uid] ?? [];
            if ($me_info) {
                ChatUser::where('uid', $uid)->update([
                    'avatar'       => $me_info['avatar'],
                    'name'         => $me_info['name'],
                    'last_operate' => $me_info['last_operate'],
                    'description'  => $me_info['description'],
                    'birthday'     => $me_info['birthday'],
                ]);
            }
        }

        $chats = Chat::where('from_uid', $uid)
            ->orWhere('target_uid', $uid)
            ->select('from_uid', 'target_uid', 'created_at')
            ->get();
        // 插入到redis
        foreach ($chats as $chat) {
            $target_uid = $chat->from_uid == $uid ? $chat->target_uid : $chat->from_uid;
            Redis::zadd("chat:{$uid}", $chat->created_at->timestamp, $target_uid);
        }

        $users = $this->getUserList($uid);
        return response()->json([
            'users' => $users,
            'start' => Carbon::parse($start)->diffForHumans(),
        ]);
    }

    /**
     * 搜索用户
     *
     * @param string $keyword
     *
     * @return JsonResponse
     */
    public function search(string $keyword): JsonResponse
    {
        $users = [];
        if (! empty($keyword)) {
            $keyword = str_replace(['.', '_', '^', '*', '%'], ['\.', '\_', '\^', '\*', '\%'], $keyword);
            $users  = DB::connection('domestic')
                ->table('users')
                ->where('name', 'like', "{$keyword}%")
				//->orderBy('last_operate', 'desc')
				->limit(6)
                ->get();
            if (! empty($users)) {
                $insert_data = [];
                foreach ($users as $k => $user) {
					if (empty($user->uid)
							|| empty($user->avatar)
							|| empty($user->name)) {
                        unset($users[$k]);
						continue;
					}
                    $insert_data[] = [
                        'uid'          => $user->uid,
                        'name'         => $user->name,
                        'avatar'       => $user->avatar,
                        'last_operate' => $user->last_operate,
                        'description'  => $user->description,
                        'birthday'     => $user->birthday,
                        'height'       => $user->height,
                        'weight'       => $user->weight,
                        'role'         => $user->role,
                    ];
                    $user->last_operate = Carbon::parse($user->last_operate)->diffForHumans();
                }
                ChatUser::insertOrIgnore($insert_data);
            }
        }
        return response()->json(compact('users'));

    }
}
