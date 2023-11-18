<?php

namespace App\Http\Controllers\Traits;

use App\Models\Chat;
use App\Models\ChatUser;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
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
        $size  = 20;
        $users = Redis::zrevrange("chat:{$me}", ($page - 1) * $size, $page * $size - 1, 'WITHSCORES');

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
                $user->last_chat_time = Carbon::parse(intval($time))->diffForHumans();
                // 获取聊天记录
                $chats = Chat::where(function($query) use ($me, $user) {
                    $query->where('from_uid', $me)
                        ->where('target_uid', $user->uid);
                    })
                    ->orWhere(function($query) use ($me, $user) {
                        $query->where('from_uid', $user->uid)
                            ->where('target_uid', $me);
                    })
                    ->get();
                $user->has_image = false;
                $user->is_dating = false;
                $user->chat_count = $chats->count();
				$content = $chats[0]->contents;
				if (str_starts_with($content, 'http')) {
				    $content = '[图片]';
				} elseif (str_starts_with($content, 'RU')) {
				    $content = '[私图]';
				}
				$user->chat_content = mb_substr($content, 0, 18);
				if ($user->chat)
                $user->name       = mb_substr($user->name, 0, 20) . ($user->note ? '(' . $user->note . ')' : '');
//                $pattern = '/(到了|开门|到楼下了|有门禁吗|打车了|上车了)/';
//                foreach ($chats as $chat) {
//                    if (! $user->has_image && str_starts_with($chat->contents, 'http')) {
//                        $user->has_image = true;
//                    }
//                    if (! $user->is_dating) {
//                        if (preg_match($pattern, $chat->contents)) {
//                            dd($chat->contents);
//                            $user->is_dating = true;
//                        }
//                    }
//                    if ($user->has_image && $user->is_dating) {
//                        break;
//                    }
//                }
//                $user->chat_count = $chats->count();
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
     * @throws \Exception
     */
    private function retrieveChats(int $me, string $start, string $end, int $limit = 10000): array|Items
    {
        $error = '';
        try {
            $res = Http::withHeader('X-REQUEST-ID', md5(time() . rand(1000, 9999)))
                ->timeout(5)
                ->get('10.120.208.16:8004/api-chatlog/recent/query', [
                    'uid'       => $me,
                    'direction' => 'both',
                    'beginDate' => "$start",
                    'endDate'   => date('Y-m-d H:i:s'),
                    'limit'     => $limit,
                ]);
            $content = $res->body();
        } catch (\Exception $e) {
            $error = $e->getMessage();
            $content = '';
        }
        $data = [];
        if (! empty($content)) {
            try {
                $data = Items::fromString($content, ['pointer' => ['/data']]);
            } catch (\JsonMachine\Exception\JsonMachineException $e) {
                $error = $e->getMessage();
            }
        }
        if (! empty($error)) {
            throw new \Exception($error);
        }
        return $data;
    }
}
