<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=0">
    <title>{{$me->name}}</title>
    <link rel="stylesheet" href="/chat/chat_user.css">
</head>
<body>
<div class="chat-container">
    <div class="alert alert-warning" id="alertWarning"></div>
    <div class="chat-refresh"><a href="/chat/user/{{$me->uid}}/refresh"><img src="/chat/refresh.jpeg" data-target={{$me->uid}}></a></div>
    @foreach($users as $user)
        <div class="chat">
            <div class="chat-title" data-name={{$user->name}}>
                <a href='b7oaXl.html'>{{$user->name}}</a>
                <div class="title-basic">{{$user->height}}/{{$user->weight}}{{$user->role >= 0 ? '/' . $user->role : ''}}</div>
            </div>
            <div class="chat-content">
                <div class="chat-left">
                    <div class="avatar">
                        <a href="{{url('/chat/user/'.$user->uid)}}"><img src="{{$user->avatar}}"/></a>
                        <a href=""><img src="{{$me->avatar}}"/></a>
                    </div>
                    <div class="time">{{$user->last_chat_time}}</div>
                    <div class="more"><a href="{{url('/chat/'.$me->uid.'/'.$user->uid)}}">>>> more</a></div>
                </div>
             </div>
        </div>
    @endforeach
</div>
</body>
</html>
