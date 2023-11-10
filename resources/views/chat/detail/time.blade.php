@if($chat->created_at->isToday())
    <!-- 如果是今天，只展示小时 -->
    <div class="time">{{$chat->created_at->format('H:i')}}</div>
@elseif ($chat->created_at->isYesterday())
    <!-- 如果是昨天，展示昨天和小时 -->
    <div class="time">昨天 {{$chat->created_at->format('H:i')}}</div>
@elseif ($chat->created_at->isCurrentYear())
    <!-- 如果是今年，展示月日和小时 -->
    <div class="time">{{$chat->created_at->format('m-d H:i')}}</div>
@else
    <!-- 如果是去年，展示年月日和小时 -->
    <div class="time">{{$chat->created_at}}</div>
@endif

