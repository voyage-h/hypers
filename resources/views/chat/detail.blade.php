<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=0">
    <title>{{$me->name}}</title>
    <link rel="stylesheet" href="/chat/detail.css">
    <script src="/chat/detail.js"></script>
</head>
<body>
<!-- 弹窗容器 -->
<div class="modal" id="myModal">
    <div class="modal-content">
        <span class="close">&times;</span>
        <h2>用户信息</h2>
        <form id="userForm" enctype="multipart/form-data">
            <input type="hidden" id="target" name="target" value="">
            <label for="name">修改备注:</label>
            <input type="text" id="note" name="note"><br><br>
            <label for="avatar">真实头像:</label>
            <input type="file" id="real_avatar" name="real_avatar" accept="image/*"><br><br>
            <label for="remove_real_avatar">是否删除真实头像:</label>
            <input type="radio" id="remove_real_avatar" name="remove_real_avatar" value=1>
            <label for="remove_real_avatar_yes">是</label>
            <input type="radio" id="remove_real_avatar_no" name="remove_real_avatar" value=0 checked>
            <label for="remove_real_avatar_no">否</label>
            <div class="submit-container">
                <button type="submit">提交</button>
            </div>
        </form>
    </div>
</div>

<div class="chat-container">
    @switch(request()->input('show'))
        @case('follow')
        <div class="alert alert-warning">关注成功</div>
        @break
        @case('unfollow')
        <div class="alert alert-warning">取消关注成功</div>
        @break
    @endswitch
    <div class="chat-refresh"><a href="/chat/detail/{{$me->uid}}/refresh" id="refreshButton" data-target="{{$target->uid}}}"><img src="/chat/refresh.png"></a></div>
    <div class="chat-home"><a href="/"><img src="/chat/home.png"></a></div>
    <div class="chat">
        <div class="chat-title" data-name={{$target->name}}>
            <a href="#">{{$target->name}}{{$target->note ? '(' . $target->note->note . ')' : ''}}</a>
            <div class="title-basic">{{$target->height}}/{{$target->weight}}/{{$target->role}}</div>
            <div class="title-more" data-target={{$target->uid}}><a href="/chat/user/{{$me->uid}}/follow/{{$target->uid}}">•••</a></div>
        </div>
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
</html>
