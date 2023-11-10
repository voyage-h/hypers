<?php

namespace App\Http\Middleware;

use Closure;
use Hashids\Hashids;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckHashUid
{
    /**
     * Handle an incoming request.
     *
     * @param Closure(Request): (Response) $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (empty($request->me)) {
            return response()->json([
                'code'    => 400,
                'message' => 'me is required',
            ]);
        }
        $uid = $this->decodeHashId($request->me);
        if (empty($uid)) {
            return response()->json([
                'code'    => 400,
                'message' => 'me is invalid',
            ]);
        }
        $_REQUEST['me'] = $uid;
        if (isset($request->target)) {
            $target = $this->decodeHashId($request->target);
            if (empty($target)) {
                return response()->json([
                    'code'    => 400,
                    'message' => 'target is invalid',
                ]);
            }
            $_REQUEST['target'] = $target;
        }
        return $next($request);
    }

    /**
     * @param string $hashid
     *
     * @return int
     */
    public function decodeHashId(string $hashid): int
    {
        if (empty($hashid)) {
            return 0;
        }
        $data = self::getHashInstance()->decode($hashid);
        return $data[0] ?? 0;
    }

    /**
     * @return Hashids
     */
    private function getHashInstance(): Hashids
    {
        return new Hashids('1766', 6, 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ123567890');
    }
}
