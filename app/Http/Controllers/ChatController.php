<?php

namespace App\Http\Controllers;

use App\Models\ChatUser;
use App\Models\Chat;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\DB;

class ChatController extends Controller
{
    /**
     * 用户列表
     *
     * @return mixed
     */
    public function user(int $uid)
    {
        // 使用redis分页
        $uids = Redis::zrevrange("chat:{$uid}", 0, -1, 'WITHSCORES');
        $users = [];
        if (! empty($uids)) {
            $users = ChatUser::whereIn('uid', array_keys($uids))
                ->orderByRaw("FIELD(uid, " . implode(',', array_keys($uids)) . ")")
                ->simplePaginate(50);
            foreach ($users as $i => $user) {
                if ($user->uid == $uid) {
                    unset($users[$i]);
                }
                $user->last_chat_time = date('m-d H:i', $uids[$user->uid]);
                $chat_count = Chat::where(function($query) use ($uid, $user) {
                    $query->where('from_uid', $uid)
                        ->where('target_uid', $user->uid);
                })
                    ->orWhere(function($query) use ($uid, $user) {
                        $query->where('from_uid', $user->uid)
                            ->where('target_uid', $uid);
                    })
                    ->count();
                $user->chat_count = $chat_count;
            }
        }

		$me = ChatUser::where('uid', $uid)->first();
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

    /**
     * 刷新聊天列表
     *
     * @return mixed
     */
    public function refresh(int $uid)
    {
        // 最近20天
        $end = date('Y-m-d', strtotime('+1 day'));
        $start = date('Y-m-d', strtotime('-20 day'));
        $raw_data = $this->retrieveChats($uid, $start, $end);
        if (! empty($raw_data)) {
            // 插入数据库
            $datas = $raw_data->json()['data'] ?? [];
            $datas_arr = array_chunk($datas, 1000);
            $new_uids = [];
            foreach ($datas_arr as $datas) {
                $insert_datas = [];
                foreach ($datas as $data) {
                    $new_uids[$data['from_uid']] = 1;
                    $new_uids[$data['target']]   = 1;
                    $insert_datas[] = [
                        'from_uid' => $data['from_uid'],
                        'target_uid' => $data['target'],
                        'contents' => $data['contents'],
                        'S' => $data['S'],
                        'created_at' => $data['time'],
                    ];
                }
                //DB::table('chats')->insertOrIgnore($insert_datas);
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

        return redirect("/chat/user/{$uid}");
    }

    /**
     * @param int    $me
     * @param string $start
     * @param string $end
     * @param int    $limit
     *
     */
    private function retrieveChats(int $me, string $start, string $end, int $limit = 10000)
    {
        return Http::withHeader('X-REQUEST-ID', md5(time() . rand(1000, 9999)))
            ->get('10.120.208.16:8004/api-chatlog/recent/query', [
                'uid'       => $me,
                'direction' => 'both',
                'beginDate' => "$start 00:00:00",
                'endDate'   => "$end 00:00:00",
                'limit'     => $limit,
            ]);
    }

    private function retrieveUsers(array $uids)
    {
        if (empty($uids)) {
            return [];
        }
        $uids_arr = array_chunk($uids, 200);
        $users    = [];
        foreach ($uids_arr as $uids) {
            $_users = Http::get("http://10.160.80.133:9999/users/batch", [
                'uids'         => implode(',', $uids),
                'grant_fields' => 'uid,name,avatar,height,weight,role,description,last_operate,latitude,longitude',
            ]);
            $users = array_merge($users, $_users->json()['data'] ?? []);
        }
        return array_column($users, null,'uid');
    }

    private function updateLocation(array $me_info)
    {
        if (empty($me_info)) {
            return [];
        }
        $lat = $me_info['latitude'];
        $lng = $me_info['longitude'];
        $_local = Http::get("https://restapi.amap.com/v3/geocode/regeo?parameters", [
            'key' => '0ad23cbd2c0bd21b4b4fa5b84f2fe763',
            'location' => "$lng,$lat",
        ]);
        $_local = $_local->json()['regeocode'] ?? [];
        $locations = [
            'uid' => $me_info['uid'],
            'last_operate' => $me_info['last_operate'],
            'latitude' => $me_info['latitude'],
            'longitude' => $me_info['longitude'],
            'address'   => $_local['formatted_address'] ?? '',
            'extra' => json_encode($_local['addressComponent'], JSON_UNESCAPED_UNICODE),
            'created_at' => time()
        ];
        DB::table('locations')->insertOrIgnore($locations);
    }
}
