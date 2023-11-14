<?php

namespace App\Http\Controllers\Traits;

use App\Models\Chat;
use App\Models\ChatUser;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Redis;
use JsonMachine\Items;

trait ChatTrait
{
    /**
     * 获取用户列表
     *
     * @param int $me
     *
     * @return array
     */
    public function getUserList(int $me)
    {
        // 使用redis分页
        $page  = (int) request()->input('page', 1);
        $users = Redis::zrevrange("chat:{$me}", ($page - 1) * 40, $page * 40 - 1, 'WITHSCORES');

        if (empty($users)) {
            return [];
        }

        $uids = array_keys($users);

        // 获取用户信息
        return ChatUser::whereIn('uid', $uids)
            ->with('note')
            ->orderByRaw("FIELD(uid, " . implode(',', $uids) . ")")
            ->get()
            ->map(function ($user) use ($me, $users) {
                $time = $users[$user->uid];
                if (date('Y-m-d') == date('Y-m-d', $time)) {
                    $user->last_chat_time = date('H:i', $time);
                } else {
                    $user->last_chat_time = date('m-d H:i', $time);
                }
                $chat_count = Chat::where(function($query) use ($me, $user) {
                    $query->where('from_uid', $me)
                        ->where('target_uid', $user->uid);
                })
                    ->orWhere(function($query) use ($me, $user) {
                        $query->where('from_uid', $user->uid)
                            ->where('target_uid', $me);
                    })
                    ->count();
                $user->chat_count = $chat_count;
                return $user;
            });
    }

    /**
     * 获取实时数据
     *
     * @param int    $me
     * @param string $start
     * @param string $end
     * @param int    $limit
     *
     * @return array|Items
     */
    private function retrieveChats(int $me, string $start, string $end, int $limit = 10000): array|Items
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
            return [];
        }
        try {
            $data = Items::fromString($res->body(), ['pointer' => ['/data']]);
        } catch (\JsonMachine\Exception\JsonMachineException $e) {
            return [];
        }
        return $data;
    }
}
