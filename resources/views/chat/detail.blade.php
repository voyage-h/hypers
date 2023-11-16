<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=0">
    <title>{{$me->name}}</title>
    <link rel="stylesheet" href="/chat/detail.css">
    <link rel="stylesheet" href="/photoswipe/photoswipe.css">
    <script src="/chat/detail.js"></script>
</head>
<body>
<div class="chat-container">
    <div class="chat-refresh"><a href="/chat/detail/{{$me->uid}}/refresh" id="refreshButton" data-target="{{$target->uid}}}"><img src="/chat/refresh.png"></a></div>
    <div class="chat-home"><a href="/"><img src="/chat/home.png"></a></div>
    <div class="chat" id="chats">
        <div class="chat-title" data-name={{$target->name}}>
            <a href="#">{{$target->name}}{{$target->note ? '(' . $target->note->note . ')' : ''}}</a>
            <div class="title-basic">{{$target->height}}/{{$target->weight}}/{{$target->role}}</div>
            <div class="title-more" data-target={{$target->uid}}><a href="/chat/user/{{$me->uid}}/follow/{{$target->uid}}">•••</a></div>
        </div>
        @php $i = 0; @endphp
        @foreach($chats as $key => $chat)
        <div class="chat-content">
            <div class="chat-{{$chat->from_uid != $me->uid ? 'left': 'right'}}">
                @if($key == 0)
                    <div class="time">
                    @include('chat.detail.time', ['time' => $chat->created_at])
                    </div>
                @elseif($chat->created_at->diffInMinutes($chats[$key - 1]->created_at) > 5)
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
</div>
</body>
<script type="module">
    import Lightbox from '/photoswipe/photoswipe-lightbox.esm.min.js';
    const lightbox = new Lightbox({
        gallery: '.chat',
        children: '.contents-img-a',
        showHideAnimationType: 'zoom',
        bgOpacity: 1,
        loop: false,
        pswpModule: () => import('/photoswipe/photoswipe.esm.min.js')
    });
    lightbox.init();
    window.pswpLightbox = lightbox;
</script>
</html>
