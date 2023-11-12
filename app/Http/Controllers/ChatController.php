<?php

namespace App\Http\Controllers;

use App\Models\ChatUser;
use App\Models\Chat;
use App\Models\UserDevice;
use Hashids\Hashids;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\DB;
use JsonMachine\Items;

class ChatController extends Controller
{
    /**
     * 首页
     *
     */
    public function index()
    {
        $users = ChatUser::where('is_suspect', 1)
            ->with('note', 'location')
            ->orderBy('last_operate', 'desc')
            ->get();
        return view('chat.index', compact('users'));
    }

    /**
     * 用户聊天列表
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
                ->with('note')
                ->orderByRaw("FIELD(uid, " . implode(',', array_keys($uids)) . ")")
                ->simplePaginate(10);

            foreach ($users as $i => $user) {
                if ($user->uid == $uid) {
                    unset($users[$i]);
                }
				$time = $uids[$user->uid];
				if (date('Y-m-d') == date('Y-m-d', $time)) {
                    $user->last_chat_time = date('H:i', $time);
				} else {
                    $user->last_chat_time = date('m-d H:i', $time);
				}
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

        $me = ChatUser::where('uid', $uid)
            ->with(['others' => function($q) use ($uid) {
                $q->where('uid', '!=', $uid); // 排除当前用户的uid
            }])->first();

        $me->hashid = $this->hashid($uid);
        $d = new \DateTime();
        $d->setTimestamp($me->birthday);
        $interval = $d->diff(new \DateTime('now'), true);
        $me->age = $interval->y;
        // 如果是post请求，返回json
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            return response()->json([
                'users' => $users,
                'me'    => $me,
            ]);
        }
        return view('chat.user', compact('users', 'me'));
    }

    private function hashid(int $uid)
    {
        $hashids = new Hashids('1766', 6, 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ123567890');
        return $hashids->encode($uid);
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
        $start = date('Y-m-d', strtotime('-10 day'));
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

        return redirect("/chat/user/{$uid}");
    }

    /**
     * @param int    $me
     * @param string $start
     * @param string $end
     * @param int    $limit
     *
     * @return Items|void
     */
    private function retrieveChats(int $me, string $start, string $end, int $limit = 10000)
    {
        try {
            $res = Http::withHeader('X-REQUEST-ID', md5(time() . rand(1000, 9999)))
                ->timeout(5)
                ->get('10.120.208.16:8004/api-chatlog/recent/query', [
                    'uid'       => $me,
                    'direction' => 'both',
                    'beginDate' => "$start 00:00:00",
                    'endDate'   => date('Y-m-d H:i:s'),
                    'limit'     => $limit,
                ]);
        } catch (\Exception $e) {
            dd($e->getMessage());
            return [];
        }
        try {
            $data = Items::fromString($res->body(), ['pointer' => ['/data']]);
        } catch (\JsonMachine\Exception\JsonMachineException $e) {
            echo $e->getMessage() . PHP_EOL;
            exit;
        }
        return $data;
    }

    /**
     * @param array $uids
     *
     * @return array
     */
    private function retrieveUsers(array $uids)
    {
        if (empty($uids)) {
            return [];
        }
        $uids_arr = array_chunk($uids, 200);
        $users    = [];
        foreach ($uids_arr as $uids) {
            try {
                $_users = Http::timeout(2)
                    ->get("http://10.160.80.133:9999/users/batch", [
                        'uids'         => implode(',', $uids),
                        'grant_fields' => 'uid,name,avatar,height,weight,role,description,last_operate,latitude,longitude,birthday,dev_id',
                    ]);
            } catch (\Exception $e) {
                break;
            }
            $users = array_merge($users, $_users->json()['data'] ?? []);
        }
        return array_column($users, null,'uid');
    }

    /**
     * @param array $me_info
     *
     * @return array|void
     */
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
        DB::table('chat_users')->where('uid', $me_info['uid'])->update([
            'description' => $me_info['description'],
            'last_operate' => $me_info['last_operate'],
            'birthday' => $me_info['birthday'],
        ]);
        if ($me_info['dev_id']) {
            Db::table('user_device')->insertOrIgnore([
                'uid'    => $me_info['uid'],
                'dev_id' => $me_info['dev_id'],
                'created_at' => time(),
            ]);
        }
    }

    public function follow(int $me, int $target)
    {
        $user = ChatUser::where('uid', $target)->first();
        $user->is_suspect = $user->is_suspect == 1 ? 0 : 1;
        $show = $user->is_suspect == 1 ? 'follow' : 'unfollow';
        $user->save();
        if (empty($me)) {
            return redirect("/chat/user/{$target}");
        }
        return redirect("/chat/$me/{$target}?show=$show");
    }

    public function indexRefresh()
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
