<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=0">
    <title>{{env('APP_NAME')}}</title>
    <link rel="stylesheet" href="/chat/chat_detail.css">
    <script src="/chat/doubleclick_scroll.js"></script>
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
    <div class="alert alert-warning" id="alertWarning"></div>
    <div class="chat-refresh"><img id="refreshButton" src="/chat/refresh.jpeg" data-target=KmBGa2></div>
    <div class="chat">
        <div class="chat-title" data-name={{$chats[0]->name}}>
            <a href='b7oaXl.html'>{{$chats[0]->name}}</a>
            <div class="title-basic">179/67</div>
            <div class="title-more" data-target=b7oaXl>•••</div>
        </div>
        @foreach($chats as $key => $chat)
            <div class="chat-content">
                <div class="chat-{{$chat->from_uid != $me->uid ? 'left': 'right'}}">
                    @if($key == 0)
                        @include('chat.detail.time')
                    @elseif($chat->created_at->diffInMinutes($chats[$key - 1]->created_at) > 5)
                        @include('chat.detail.time')
                    @endif
                    <div class="avatar">
                        <a href="/location/list?target=b7oaXl"><img src="{{$chat->avatar}}"/></a>
                    </div>
                    @include('chat.detail.content')
                </div>
            </div>
        @endforeach
    </div>
</div>
</body>
</html>
