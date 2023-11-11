<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=0">
    <title>{{env('APP_NAME')}}</title>
    <link rel="stylesheet" href="/chat/chat_index.css">
</head>
<body>
<div class="chat-refresh"><a href="/chat/index/refresh"><img src="/chat/refresh.png"></a></div>
<div class="container">
    @foreach($users as $user)
        <div class="user-container">
        <div class="user" data-id="{{$user->id}}">
            <a href="/chat/user/{{$user->uid}}">
            <div class="avatar">
                <img src="{{$user->avatar}}" alt="">
            </div>
            <div class="name">{{$user->name}}{{$user->note ? '(' . $user->note . ')' : ''}}
            </div>
            <div class="basic">{{$user->height}}/{{$user->weight}}/{{$user->role}}</div>
            <div class="private">{{date('m-d H:i', $user->last_operate)}}{{$user->location ? ' · ' . $user->location->address : ''}}</div>
            </a>
        </div>
        </div>
    @endforeach
</div>
</body>
</html>
