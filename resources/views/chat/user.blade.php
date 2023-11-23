<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=0">
    <title>{{$me->name ?? env('APP_NAME')}}</title>
    <link rel="stylesheet" href="{{url('/chat/user1.css')}}">
    <link rel="stylesheet" href="/photoswipe/photoswipe.css">	
    <script src="{{url('/chat/user.js')}}"></script>
</head>
<body>
<!-- 弹窗 -->
<div class="alert alert-warning" id="alertWarning">未知错误</div>
<div class="alert alert-success" id="alertSuccess">成功</div>

<!-- 菜单 -->
{{--
<div class="btn user-refresh"><a href="javascript:void(0)" id="user-refresh" data-target={{$me->uid}}><img src="{{url('/chat/location.png')}}"></a></div>
 --}}
<div class="btn chat-refresh"><a href="javascript:void(0)" id="btn-refresh" data-target={{$me->uid}}><img src="{{url('/chat/network.png')}}"></a></div>
<div class="btn chat-home"><a href="/"><img src="{{url('/chat/home.png')}}"></a></div>

<div class="chat-container">
    <div class="user" data-id="{{$me->id}}">
        <div class="user-avatar">
            <a href="https://app.blued.cn/user?id={{$me->hashid}}$&uid={{$me->hashid}}&action=profile&app=1&enc=1">
                <img id="user-avatar-low" src="{{$me->avatar}}" alt="">
                <img id="user-avatar-high" src="" alt="">
            </a>
        </div>
        <div class="user-info">
            <div class="user-name"><b id="user-name-content" data-value="{{$me->name}}">{{$me->name}}{{$me->note ? '(' . $me->note . ')' : ''}}</b>
                <img id="icon-pencil" src="/chat/pencil.png">
            </div>
            <div class="follow">
                <a href="javascript:void(0)" id="btn-follow" data-uid="{{$me->uid}}" data-value="{{$me->is_suspect}}">{{$me->is_suspect ? '取消关注' : '关注'}}</a>
            </div>
            <div class="user-basic">{{$me->age}} · {{$me->height}}cm · {{$me->weight}}kg · {{$me->role == -1 ? '其他' : $me->role}}</div>
            <div class="user-desc">{!!$me->description!!}</div>
        </div>
		<div class="seperate"></div>
        @if (! empty($me->device->others[0]))
        <div class="user-others">
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
        </div>
        @endif
{{--        <div class="location">
		    <img src="/chat/loc.png">
            {{$me->last_operate}}{{$me->location ? ' · ' . $me->location->address : ''}}
        </div>
 --}}
        <div class="menu">
            <div class="menu-icon"><img src="/chat/loc.png"></div>
            <div class="menu-title">{{$me->last_operate}}{{$me->location ? ' · ' . $me->location->address : ''}}</div>
            <div class="menu-right menu-right-refresh" id="user-refresh" data-target={{$me->uid}}><img src="/chat/icon-refresh.png"></div>
        </div>
		<div class="seperate"></div>
        <a class="menu-a" href="/chat/{{$me->uid}}/my_album">
            <div class="menu menu-active">
                <div class="menu-icon chat-albums-icon"><img src="/chat/album-icon.png"></div>
                <div class="menu-title chat-albums-title">我的</div>
                <div class="menu-right chat-albums-right"><img src="/chat/right.png"></div>
            </div>
        </a>
            @if(! empty($me->albums[0]))
            <div class="menu-items">
                @foreach($me->albums as $album)
                    <div class="menu-item">
					    <a href="{{$album->contents}}" class="contents-img-a" data-pswp-src="{{$album->contents}}!o.png">
						    <img src="{{$album->contents}}">
						</a>
					</div>
                @endforeach
            </div>
            @endif
		<div class="seperate"></div>
        <a class="menu-a" href="/chat/{{$me->uid}}/all">
        <div class="menu menu-active">
            <div class="menu-icon chat-albums-icon"><img src="/chat/chat.png"></div>
            <div class="menu-title chat-albums-title">全览</div>
            <div class="menu-right chat-albums-right"><img src="/chat/right.png"></div>
        </div>
        </a>
		<div class="seperate"></div>
        <a class="menu-a" href="/chat/{{$me->uid}}/album">
            <div class="menu menu-active">
                <div class="menu-icon chat-albums-icon"><img src="/chat/album-icon.png"></div>
                <div class="menu-title chat-albums-title">相册</div>
                <div class="menu-right chat-albums-right"><img src="/chat/right.png"></div>
            </div>
        </a>
    </div>
	<div class="last-date" id="last-date">{{empty($start) ? '无更新记录' : "-- 更新记录于$start --"}}</div>
    <div class="chat-list" data-uid="{{$me->uid}}" data-avatar="{{$me->avatar}}">
    @foreach($users as $user)
        <div class="chat">
            <div class="chat-content">
                <div class="chat-left">
                    <div class="avatar">
                        <a href="{{url('/chat/user/'.$user->uid)}}"><img src="{{$user->avatar}}"/></a>
                        <div class="chat-name">
                            <a href='{{url('/chat/'.$me->uid.'/'.$user->uid)}}'>{{$user->name}}
                                <div class="title-basic">[互动<label class="{{$user->chat_count > 100 ? 'hot' : 'normal'}}"> {{$user->chat_count}}</label>] {{ $user->chat_content }}</div>
                            </a>
                        </div>
                        <div class="time">
                            <div class="time-content">{{$user->last_chat_time}}</div>
                        </div>
                    </div>
                </div>
             </div>
        </div>
    @endforeach
    </div>
</div>
<div class="loading-container" id="loading">
    <div class="loading"></div>
</div>
<div class="page" id="page">{{empty($start) ? '' : ' -- 没有更多 -- '}}</div>
@include('chat.detail.modal', ['uid' => $me->uid])
</body>
@include('components.photoswipe', ['gallery' => '.menu-items', 'children' => '.contents-img-a'])
</html>
