<!-- 如果以http开头 -->
@if (str_starts_with($contents, 'http'))
    <!-- 如果是视频 -->
    @if (str_contains($contents, '.mp4'))
        <div class="contents-video" data-src="{{$contents}}">
            <a class="contents-img-a" href="{{$chat->contents}}" data-pswp-src="{{$chat->contents}}">
                <img src="{{$contents}}">
            </a>
        </div>
    <!-- 如果是音频 -->
    @elseif (str_contains($contents, '.mp3'))
        <div class="contents">
		    [语音]
        </div>
    <!-- 如果图片 -->
    @elseif (preg_match('/\.(jpg|jpeg|png|gif|bmp)$/', $contents) || str_contains($contents, 'http://dl4'))
        <div class="contents-img">
            <a class="contents-img-a" href="{{$chat->contents}}!o.png" data-pswp-src="{{$chat->contents}}!o.png">
                <img src="{{$chat->contents}}" />
            </a>
        </div>
    @else
        <div class="contents">
            <a href="{{$contents}}">{{$contents}}</a>
        </div>
    @endif
@else
    <div class="contents">
        {{str_starts_with($contents, 'RU') ? '[私图]' : $contents}}
    </div>
@endif

