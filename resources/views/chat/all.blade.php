<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=0">
    <title>{{$me->name ?? env('APP_NAME')}}</title>
    <link rel="stylesheet" href="/chat/detail.css">
    <link rel="stylesheet" href="/photoswipe/photoswipe.css">
    <script src="/chat/detail.js"></script>
</head>
<body>
<div class="chat-container">
    <div class="chat-home"><a href="/"><img src="/chat/home.png"></a></div>
    @foreach($chats as $uid => $chat_arr)
    @php $target = $users[$uid == $me->uid ? $chat_arr[0]->target_uid : $uid]; @endphp
    <div class="chat">
        <div class="chat-title">
            <a href="/chat/{{$me->uid}}/{{$target->uid}}">{{$target->name}}{{$target->note ? '(' . $target->note->note . ')' : ''}}</a>
            <div class="title-basic">{{$target->height}}/{{$target->weight}}/{{$target->role}}</div>
        </div>
        @foreach($chat_arr as $key => $chat)
        <div class="chat-content">
            <div class="chat-{{$chat->from_uid != $me->uid ? 'left': 'right'}}">
                @if($key == 0)
                    <div class="time">
                    @include('chat.detail.time', ['time' => $chat->created_at])
                    </div>
                @elseif($chat->created_at->diffInMinutes($chat_arr[$key - 1]->created_at) > 5)
                    <div class="time">
                    @include('chat.detail.time', ['time' => $chat->created_at])
                    </div>
                @endif
                <div class="avatar">
                    <a href="/chat/user/{{$chat->from_uid}}"><img src="{{$chat->avatar}}"/></a>
                </div>
                @include('chat.detail.content', ['contents' => $chat->contents])
            </div>
        </div>
        @endforeach
    </div>
    @endforeach
</div>
<div class="page">{!!$page!!}</div>
</body>
@include('components.photoswipe', ['gallery' => '.chat-container', 'children' => '.contents-img-a'])
</html>
