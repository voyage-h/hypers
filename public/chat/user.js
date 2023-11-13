// 记录上一次滚动位置
var lastScrollTop = 0;
var page = 1;
var meUid;
var hasData = true;
var isLoading = false;
const accessToken = `eyJpdiI6InJUdmhibVF2RXRFaE1jZXQ2OFYrYlE9PSIsInZhbHVlIjoiVmtzb05UaTAxN3Nnbncybk9WNjc4S0Y5NDdvcnd0blAvQ2VDL0NJOStyN0xYQnExMFR1amRQYzJtbnpxSGhwUWFPbmRjOW9wdnRPeGNudGNPUUJJU0hnbHdINHd1QnhGZ2R5VS95dU9hRWpBMzdYd3QzTEp0KzFxQlc1QTdZdGsiLCJtYWMiOiJhOTdiMDFlMWM2YjA5MzAxNWFiYjgzY2FjYTBlNDBiZjU4YjVkZWZkMWQxZWFhZTg2NjVhMWY2MzFmMzVhZjIyIiwidGFnIjoiIn0%3D`;

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
        if (!isLoading && hasData && distanceToBottom < threshold) {
            meUid = document.querySelector('.chat-list').getAttribute('data-uid');
			isLoading = true;
            // 执行你想要的动作，这里是在控制台输出一条消息
            // alert('ttt');
            // 请求数据
            // 调用 getChatUsers 函数，并传递一个回调函数
            getChatUsers(function(error, res) {
					isLoading = false;
                if (res.users.data && res.users.data.length > 0) {
                    var me = res.me;
                    // 遍历数组
                    var html = '';
                    for (let k in res.users.data) {
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
                } else {
                    hasData = false;
                    console.log('没有更多数据了');
                    document.querySelector('.page').style.display = 'flex';
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
    // 添加鉴权头
    xhr.setRequestHeader('Authorization', 'Bearer ' + accessToken);
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
document.addEventListener('DOMContentLoaded', function() {
    // 警告
    var warning = document.getElementById('alertWarning');

    // 关注，取消关注
    var btnFollow = document.getElementById('btn-follow');
    btnFollow.addEventListener('click', function() {
        var uid = btnFollow.getAttribute('data-uid');
        var follow = btnFollow.getAttribute('data-value');
        var url = '/api/chat/user/follow/' + uid;
        var xhr = new XMLHttpRequest();
        xhr.open('POST', url, true);
        // 添加鉴权头
        xhr.setRequestHeader('Authorization', 'Bearer ' + accessToken);
        // 发送请求
        xhr.send();
        xhr.onreadystatechange = function() {
            if (xhr.readyState == 4) {
                if (xhr.status == 200) {
                    btnFollow.textContent = follow == 1 ? '关注' : '取消关注';
                } else {
                    warning.style.display = 'block';
                    warning.textContent = '网络错误，请稍后再试';
                    setTimeout(function() {
                        warning.style.display = 'none';
                    }, 2000);
                }
            }
        };
    });
});
