<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=0">
    <title>{{env('APP_NAME')}}</title>
    <link rel="stylesheet" href="/chat/index.css">
</head>
<body>
<div class="chat-refresh"><a href="/chat/index/refresh"><img src="/chat/refresh.png"></a></div>
<div class="container">
    @foreach($users as $user)
        <div class="user-container">
        <div class="user" data-id="{{$user->id}}">
            <a href="/chat/user/{{$user->uid}}">
            <div class="avatar">
                <img src="{{$user->avatar}}!o.png" alt="">
            </div>
            <div class="name">{{$user->name}}{{$user->note ? '(' . $user->note . ')' : ''}}
            </div>
            <div class="basic">{{$user->age ? $user->age . '/' : ''}}{{$user->height}}/{{$user->weight}}{{$user->role >= 0 ? '/'.$user->role : ''}}</div>
            <div class="private">
                @if(date('Y-m-d', $user->last_operate) == date('Y-m-d'))
                    {{date('H:i', $user->last_operate)}}
                @elseif(date('Y-m-d', $user->last_operate) == date('Y-m-d', strtotime('-1 day')))
                    昨天
                @else
                    {{date('m-d', $user->last_operate)}}
                @endif
                {{$user->location ? ' · ' . substr($user->location->address, 0, 6) : ''}}</div>
            </a>
        </div>
        </div>
    @endforeach
</div>
</body>
</html>
