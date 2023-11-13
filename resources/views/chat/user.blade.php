<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=0">
    <title>{{$me->name}}</title>
    <link rel="stylesheet" href="/chat/user.css">
    <script src="/chat/chat.js"></script>
</head>
<body>
<div class="chat-container">
    <div class="alert alert-warning" id="alertWarning"></div>
    <div class="chat-refresh"><a href="/chat/user/{{$me->uid}}/refresh"><img src="/chat/refresh.png" data-target={{$me->uid}}></a></div>
    <div class="chat-home"><a href="/"><img src="/chat/home.png"></a></div>
    <div class="user" data-id="{{$me->id}}">
        <div class="user-avatar">
            <a href="https://app.blued.cn/user?id={{$me->hashid}}$&uid={{$me->hashid}}&action=profile&app=1&enc=1">
            <img src="{{$me->avatar}}!o.png" alt="">
            </a>
        </div>
        <div class="user-info">
            <div class="user-name">{{$me->name}}{{$me->note ? '(' . $me->note . ')' : ''}}</div>
            <div class="follow">
                <a href="javascript:void(0)" id="btn-follow" data-uid="{{$me->uid}}" data-value="{{$me->is_suspect}}">
                    {{$me->is_suspect ? '取消关注' : '关注'}}
                </a>
            </div>
            <div class="user-basic">{{$me->age}} / {{$me->height}} / {{$me->weight}}{{$me->role >= 0 ? " / $me->role" : ''}}</div>
            <div class="user-desc">{{$me->description}}</div>
        </div>
        <div class="user-private">
            @include('chat.detail.time', ['time' => \Carbon\Carbon::createFromTimestamp($me->last_operate)])
            {{$me->location ? ' · ' . $me->location->address : ''}}
        </div>
    </div>
    <div class="chat-list" data-uid="{{$me->uid}}">
    @foreach($users as $user)
        <div class="chat">
            <div class="chat-title" data-name={{$user->name}}>
                <a href='{{url('/chat/'.$me->uid.'/'.$user->uid)}}'>{{$user->name}}{{$user->note ? '(' . $user->note . ')' : ''}}</a>
                <div class="title-basic">{{$user->height}} / {{$user->weight}}{{$user->role >= 0 ? ' / ' . $user->role : ''}}</div>
            </div>
            <div class="chat-content">
                <div class="chat-left">
                    <div class="avatar">
                        <a href="{{url('/chat/user/'.$user->uid)}}"><img src="{{$user->avatar}}"/></a>
                        <a href=""><img src="{{$me->avatar}}"/></a>
                    </div>
                    <div class="time">{{$user->last_chat_time}} · 互动 <label class="{{$user->chat_count > 100 ? 'hot' : ''}}">{{$user->chat_count}}</label> 次</div>
                    <div class="more"><a href="{{url('/chat/'.$me->uid.'/'.$user->uid)}}"><b>>>> more</b></a></div>
                </div>
             </div>
        </div>
    @endforeach
    </div>
</div>
<div class="page"> -- 没有更多 -- </div>
{{--@if($users)--}}
{{--<div class="page">{{$users->links()}}</div>--}}
{{--@endif--}}
</body>
</html>
