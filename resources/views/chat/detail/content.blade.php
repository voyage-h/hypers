@if (strpos($contents, 'http') === 0)
    <!-- 如果以http开头 -->
    <div class="contents-img">
        <a href="{{$contents}}!o.png"><img src="{{$contents}}" /></a>
    </div>
@elseif (strpos($contents, 'RU') === 0)
    <!-- 如果以RU开头 -->
    <div class="contents">[私图]</div>
@else
    <div class="contents">{{ $contents }}</div>
@endif
