<!-- 如果以http开头 -->
@if (str_starts_with($contents, 'http'))
    <!-- 如果是视频 -->
    @if (str_contains($contents, '.mp4'))
        <div class="contents-video" data-src="{{$contents}}">
            <a href="{{$contents}}"><img src="{{$contents}}"></a>
        </div>
    <!-- 如果是音频 -->
    @elseif (str_contains($contents, '.mp3'))
        <div class="contents">
		    [语音]
        </div>
    <!-- 如果是其他文件 -->
    @else
        <div class="contents-img">
            <img class="contents-img-a" src="{{$contents}}" />
        </div>
    @endif
@else
    <div class="contents">
        {{str_starts_with($contents, 'RU') ? '[私图]' : $contents}}
    </div>
@endif

