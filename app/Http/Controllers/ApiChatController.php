<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Traits\ChatTrait;
use App\Http\Controllers\Traits\UserTrait;
use App\Models\Chat;
use App\Models\ChatUser;
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
     * 刷新聊天列表
     *
     * @return mixed
     */
    public function refresh(int $uid)
    {
        // 删除缓存
        Cache::tags("chat:user:$uid")->flush();

        // 最近20天
        $end = date('Y-m-d', strtotime('+1 day'));
        $start = date('Y-m-d', strtotime('-10 day'));
        $raw_data = $this->retrieveChats($uid, $start, $end);

        // 获取多账号信息
        $other_uids = $this->getOtherUids($uid);
        if (! empty($other_uids)) {
            $insert_device_data = [];
            foreach ($other_uids as $other_uid => $dev_id) {
                $insert_device_data[] = [
                    'uid'    => $other_uid,
                    'dev_id' => $dev_id,
                    'created_at' => time(),
                ];
            }
            if (! empty($insert_device_data)) {
                DB::table('user_device')->insertOrIgnore($insert_device_data);
            }
            // 插入信息
            $other_users = $this->retrieveUsers(array_keys($other_uids));
            foreach ($other_users as $k => $other_user) {
                unset($other_users[$k]['latitude']);
                unset($other_users[$k]['longitude']);
                unset($other_users[$k]['dev_id']);
            }
            DB::table('chat_users')->insertOrIgnore(array_values($other_users));
        }

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
            $me_info = $new_users[$uid] ?? [];
            if ($me_info) {
                $this->updateLocation($me_info);
            }
            foreach ($new_users as $k => $new_user) {
                unset($new_users[$k]['latitude']);
                unset($new_users[$k]['longitude']);
                unset($new_users[$k]['dev_id']);
            }
            DB::table('chat_users')->insertOrIgnore(array_values($new_users));
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

        return $this->user($uid);
    }
}
