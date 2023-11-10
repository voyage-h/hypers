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
    <div class="chat-refresh"><img id="refreshButton" src="/chat/refresh.jpeg" data-target=KmBGa2></div>
    @foreach($users as $user)
        <div class="chat">
            <div class="avatar">
                <a href="{{url('/chat/detail/'.$me->uid.'/with/'.$user->uid)}}"><img src="{{$user->avatar}}"/></a>
                <a href="{{url('/chat/detail/'.$me->uid.'/with/'.$user->uid)}}"><img src="{{$me->avatar}}"/></a>
            </div>
            <div class="chat-title" data-name={{$user->name}}>
                <a href='b7oaXl.html'>{{$user->name}}</a>
                <div class="title-basic">{{$user->height}}/{{$user->weight}}</div>
            </div>
        </div>
    @endforeach
</div>
</body>
</html>
