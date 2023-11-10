@if (strpos($chat->contents, 'http') === 0)
    <!-- 如果以http开头 -->
    <div class="contents-img">
        <a href="{{$chat->contents}}!o.png"><img src="{{$chat->contents}}" /></a>
    </div>
@elseif (strpos($chat->contents, 'RU') === 0)
    <!-- 如果以RU开头 -->
    <div class="contents">[私图]</div>
@else
    <div class="contents">{{ $chat->contents }}</div>
@endif
