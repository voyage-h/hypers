@if($time->isToday())
    <!-- 如果是今天，只展示小时 -->
    {{$time->format('H:i')}}
@elseif ($time->isYesterday())
    <!-- 如果是昨天，展示昨天和小时 -->
    昨天 {{$time->format('H:i')}}
@elseif ($time->isCurrentYear())
    <!-- 如果是今年，展示月日和小时 -->
    {{$time->format('m-d H:i')}}
@else
    <!-- 如果是去年，展示年月日和小时 -->
    {{$time}}
@endif

