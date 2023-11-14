<?php

namespace App\Http\Controllers\Traits;

use Hashids\Hashids;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

trait UserTrait
{
    private function getHashInstance(): Hashids
    {
        return new Hashids('1766', 6, 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ123567890');
    }

    /**
     * 加密Uid
     *
     * @param $uid
     *
     * @return string
     */
    public function encodeUid($uid): string
    {
        return $this->getHashInstance()->encode($uid);
    }

    /**
     * 解密Uid
     *
     * @param $uid
     *
     * @return int
     */
    public function decodeUid($uid): int
    {
        $hash = $this->getHashInstance()->decode($uid);
        return intval($hash[0] ?? 0);
    }

    /**
     * 获取单个用户数据
     *
     * @param int   $uid
     * @param array $fields
     *
     * @return array
     */
    public function retrieveUser(int $uid, array $fields = []): array
    {
        return $this->_retrieveUsers([$uid], $fields)[$uid] ?? [];
    }

    /**
     * 获取实时用户数据
     *
     * @param array $uids
     * @param array $fields
     *
     * @return array
     */
    public function retrieveUsers(array $uids, array $fields = []): array
    {
        return $this->_retrieveUsers($uids, $fields);
    }

    /**
     * @param array $uids
     * @param array $fields
     *
     * @return array
     */
    private function _retrieveUsers(array $uids, array $fields): array
    {
        if (empty($uids)) {
            return [];
        }
        if (empty($fields)) {
            $fields = ['uid', 'name', 'avatar', 'height', 'weight', 'role', 'description', 'last_operate', 'latitude', 'longitude', 'birthday', 'dev_id'];
        }
        $uids_arr = array_chunk($uids, 200);
        $users    = [];
        foreach ($uids_arr as $uids) {
            try {
                $_users = Http::timeout(5)
                    ->get("http://10.160.80.133:9999/users/batch", [
                        'uids'         => implode(',', $uids),
                        'grant_fields' => implode(',', $fields),
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
    public function updateLocation(array $me_info)
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

    /**
     * @param string $dev_id
     *
     * @return array
     */
    public function getUidsByDevice(string $dev_id): array
    {
        if (empty($dev_id)) {
            return [];
        }
        $data = DB::connection('domestic')->table('users_dev')
            ->where('dev_id', $dev_id)
            ->get();

        return array_column($data->toArray(), 'uid');
    }

    /**
     * 获取多账号
     *
     * @param int $uid
     *
     * @return array
     */
    public function getOtherUids(int $uid): array
    {
        if (empty($uid)) {
            return [];
        }
        $device = DB::connection('domestic')
            ->table('users_dev')
            ->where('uid', $uid)->first();
        $users  = DB::connection('domestic')
            ->table('users_dev')
            ->where('dev_id', $device->dev_id)
            ->where('uid', '!=', $uid)
            ->get();
        return array_column($users->toArray(),'dev_id', 'uid');
    }
}
