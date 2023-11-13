<!-- 弹窗容器 -->
<div class="modal" id="myModal">
    <div class="modal-content">
        <span class="close">&times;</span>
        <h2>用户信息</h2>
        <form id="userForm" enctype="multipart/form-data">
            <input type="hidden" id="modal-uid" name="uid" value="{{$uid}}">
            <label for="name">修改备注:</label>
            <input type="text" id="modal-note" name="note"><br><br>
{{--            <label for="avatar">真实头像:</label>--}}
{{--            <input type="file" id="real_avatar" name="real_avatar" accept="image/*"><br><br>--}}
            <div class="submit-container">
                <button type="submit">提交</button>
            </div>
        </form>
    </div>
</div>
