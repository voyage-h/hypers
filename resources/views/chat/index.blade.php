<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=0">
    <title>{{env('APP_NAME')}}</title>
    <link rel="stylesheet" href="/chat/index.css">
    <script src="/chat/index.js"></script>
</head>
<body>
<div class="chat-refresh"><a href="/chat/index/refresh"><img src="/chat/user_refresh.png"></a></div>
<div class="alert alert-warning" id="alertWarning">未知错误</div>
<div class="search">
    <div class="search-input">
        <input id="search-input" type="text" name="name" placeholder="搜索">
    </div>
    <div class="search-btn">
        <img id="search-btn" src="/chat/search.png">
        <img id="remove-btn" src="/chat/remove.png">
    </div>
    <div class="search-users"></div>
</div>
<div class="container">
    @foreach($users as $user)
        <div class="user-container">
        <div class="user" data-id="{{$user->id}}">
            <a href="/chat/user/{{$user->uid}}">
            <div class="avatar">
                <img src="{{$user->avatar}}" alt="">
            </div>
            <div class="online-status{{$user->is_online ? ' status-active': ''}}"></div>
            <div class="info">
                <div class="name">{{mb_substr($user->name, 0, 6) . ($user->note ? '(' . $user->note . ')' : '')}}</div>
                <div class="private">
                    {{$user->last_operate}}{{$user->location ? ' · ' . substr($user->location->address, 0, 6) : ''}}
                </div>
            </div>
            </a>
        </div>
        </div>
    @endforeach
</div>
</body>
</html>
