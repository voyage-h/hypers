<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=0">
    <title>{{$me->name}}</title>
    <link rel="stylesheet" href="{{url('/chat/user.css')}}">
    <script src="{{url('/chat/user.js')}}"></script>
</head>
<body>
<div class="alert alert-warning" id="alertWarning">未知错误</div>
<div class="alert alert-success" id="alertSuccess">成功</div>
<div class="chat-container">
    <div class="btn user-refresh"><a href="javascript:void(0)" id="user-refresh" data-target={{$me->uid}}><img src="{{url('/chat/user_refresh.png')}}"></a></div>
    <div class="btn chat-refresh"><a href="javascript:void(0)" id="btn-refresh" data-target={{$me->uid}}><img src="{{url('/chat/refresh.png')}}"></a></div>
    <div class="btn chat-home"><a href="/"><img src="{{url('/chat/home.png')}}"></a></div>
    <div class="user" data-id="{{$me->id}}">
        <div class="user-avatar">
            <a href="https://app.blued.cn/user?id={{$me->hashid}}$&uid={{$me->hashid}}&action=profile&app=1&enc=1">
            <img src="{{$me->avatar}}!o.png" alt="">
            </a>
        </div>
        <div class="user-info">
            <div class="user-name"><b id="user-name-content" data-value="{{$me->name}}">{{mb_substr($me->name, 0, 15)}}{{$me->note ? '(' . $me->note . ')' : ''}}</b>
                <img id="icon-pencil" src="/chat/pencil.png">
            </div>
            <div class="follow">
                <a href="javascript:void(0)" id="btn-follow" data-uid="{{$me->uid}}" data-value="{{$me->is_suspect}}">{{$me->is_suspect ? '取消关注' : '关注'}}</a>
            </div>
            <div class="user-basic">{{$me->age}} / {{$me->height}} / {{$me->weight}}{{$me->role >= 0 ? " / $me->role" : ''}}</div>
            <div class="user-desc">{{$me->description}}</div>
        </div>
        <div class="user-others">
            @if (! empty($me->device->others))
            @foreach($me->device->others as $others)
	            @if ($others->user)
                <div class="user-other">
                    <div class="other-avatar">
                        <a href="/chat/user/{{$others->user->uid}}">
                            <img src="{{$others->user->avatar}}" alt="">
                        </a>
                    </div>
                </div>
				@endif
            @endforeach
            @endif
        </div>
    </div>
    <div class="location">
        @include('chat.detail.time', ['time' => \Carbon\Carbon::createFromTimestamp($me->last_operate)])
        {{$me->location ? ' · ' . $me->location->address : ''}}
    </div>
	@if (! empty($start))
	<div class="last-date">-- {{$start}} --</div>
	@endif
    <div class="chat-list" data-uid="{{$me->uid}}" data-avatar="{{$me->avatar}}">
    @foreach($users as $user)
        <div class="chat">
            <div class="chat-title" data-name={{$user->name}}>
                <a href='{{url('/chat/'.$me->uid.'/'.$user->uid)}}'>{{$user->name}}{{$user->note ? '(' . $user->note . ')' : ''}}
                <div class="title-basic">{{$user->height}} {{$user->weight}} {{$user->role >= 0 ? $user->role : ''}}</div>
                </a>
            </div>
            <div class="chat-content">
                <div class="chat-left">
                    <div class="avatar">
                        <a href="{{url('/chat/user/'.$user->uid)}}"><img src="{{$user->avatar}}"/></a>
                        <a href=""><img src="{{$me->avatar}}"/></a>
                    </div>
                    <div class="time">{{$user->last_chat_time}} · 互动 <label class="{{$user->chat_count > 100 ? 'hot' : 'normal'}}">{{$user->chat_count}}</label> 次</div>
                    <div class="more"><a href="{{url('/chat/'.$me->uid.'/'.$user->uid)}}"><b>>>> more</b></a></div>
                </div>
             </div>
        </div>
    @endforeach
    </div>
</div>
<div class="page"> -- 没有更多 -- </div>
@include('chat.detail.modal', ['uid' => $me->uid])
</body>
</html>
