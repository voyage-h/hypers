// 记录上一次滚动位置
var lastScrollTop = 0;
var page = 1;
var meUid;

// 监听滚动事件
window.addEventListener('scroll', function() {
    // 获取当前滚动位置
    var currentScrollTop = window.pageYOffset || document.documentElement.scrollTop;

    // 如果用户向下滑动
    if (currentScrollTop >= lastScrollTop) {
        // 计算页面底部距离
        var distanceToBottom = document.body.offsetHeight - (currentScrollTop + window.innerHeight);

        // 设置一个阈值，比如 20 像素
        var threshold = 20;

        // 如果页面滚动到底部
        if (distanceToBottom < threshold) {
            meUid = document.querySelector('.chat-list').getAttribute('data-uid');
            // 执行你想要的动作，这里是在控制台输出一条消息
            // alert('ttt');
            // 请求数据
            // 调用 getChatUsers 函数，并传递一个回调函数
            getChatUsers(function(error, res) {
                if (error) {
                    console.error('Error:', error);
                } else if (res.users.data) {
                    var me = res.me;
                    // 遍历数组
                    var html = '';
                    for (k in res.users.data) {
                        let user = res.users.data[k];
                        let labelClass = user.chat_count >= 100 ? 'hot' : 'label-default';
                        html += `
                        <div class="chat">
                        <div class="chat-title">
                            <a href='/chat/` + me.uid + `/` + user.uid + `'>` + user.name + `</a>
                            <div class="title-basic">` + user.height + '/' + user.weight + `/` + user.role + `</div>
                        </div>
                        <div class="chat-content">
                            <div class="chat-left">
                                <div class="avatar">
                                    <a href="/chat/user/` + user.uid + `"><img src="` + user.avatar + `"/></a>
                                    <a href="#"><img src="` + me.avatar + `"/></a>
                                </div>
                                <div class="time">` + user.last_chat_time + ` · 互动 <label class="` + labelClass + `">` + user.chat_count + `</label> 次</div>
                                <div class="more"><a href="/chat/` + me.uid + `/` + user.uid + `"><b>>>> more</b></a></div>
                            </div>
                         </div>
                         </div>`
                    }
                    if (html) {
                        document.querySelector('.chat-list').innerHTML += html;
                    }
                }
            });
        }
    }

    // 更新上一次滚动位置
    lastScrollTop = currentScrollTop;
});

// 请求数据
function getChatUsers(callback) {
    page += 1;
    var url = '/api/chat/user/' + meUid +'?page=' + page;
    var xhr = new XMLHttpRequest();
    xhr.open('POST', url, true);
    xhr.send();

    xhr.onreadystatechange = function() {
        if (xhr.readyState == 4) {
            if (xhr.status == 200) {
                var data = JSON.parse(xhr.responseText);
                console.log(url, data);
                callback(null, data); // 将数据传递给回调函数
            } else {
                callback(xhr.status); // 将错误状态传递给回调函数
            }
        }
    };
}
