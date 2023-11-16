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
<div id="images" style="display: none">
@foreach($chats as $key => $chat)
@if (str_starts_with($chat->contents, 'http') && ! str_contains($chat->contents, '.mp4') && ! str_contains($chat->contents, '.mp3'))
    <a class="image-a" href="{{$chat->contents}}!o.png" data-pswp-src="{{$chat->contents}}!o.png">
        <img src="{{$chat->contents}}" />
    </a>
@endif
@endforeach
</div>
<script type="module">
    import Lightbox from '/photoswipe/photoswipe-lightbox.esm.min.js';
    const lightbox = new Lightbox({
        gallery: '#images',
        children: 'a',
        showHideAnimationType: 'zoom',

        pswpModule: () => import('/photoswipe/photoswipe.esm.min.js')
    });
    // lightbox.addFilter('itemData', (itemData, index) => {
    //     if (itemData.element.getAttribute('data-pswp-src') !== null) {
    //         return itemData;
    //     }
    // });
    // lightbox.addFilter('numItems', (numItems, dataSource) => {
    //     console.log(dataSource);
    //     return numItems;
    // });
    lightbox.addFilter('domItemData', (itemData, element, linkEl) => {
        if (linkEl.dataset.pswpSrc !== undefined) {
            return itemData;
        }
    });
    lightbox.init();
    window.pswpLightbox = lightbox;
</script>
</html>
