// 记录上一次滚动位置
var lastScrollTop = 0;
var page = 0;
var meUid;
var meAvatar;
var hasData = true;
var isLoading = false;
const accessToken = `eyJpdiI6InJUdmhibVF2RXRFaE1jZXQ2OFYrYlE9PSIsInZhbHVlIjoiVmtzb05UaTAxN3Nnbncybk9WNjc4S0Y5NDdvcnd0blAvQ2VDL0NJOStyN0xYQnExMFR1amRQYzJtbnpxSGhwUWFPbmRjOW9wdnRPeGNudGNPUUJJU0hnbHdINHd1QnhGZ2R5VS95dU9hRWpBMzdYd3QzTEp0KzFxQlc1QTdZdGsiLCJtYWMiOiJhOTdiMDFlMWM2YjA5MzAxNWFiYjgzY2FjYTBlNDBiZjU4YjVkZWZkMWQxZWFhZTg2NjVhMWY2MzFmMzVhZjIyIiwidGFnIjoiIn0%3D`;

document.addEventListener('DOMContentLoaded', function() {
	const loading = document.getElementById('loading');
    const chatList = document.querySelector('.chat-list');
    const noMore = document.getElementById('page');
    meUid = chatList.getAttribute('data-uid');
    meAvatar = chatList.getAttribute('data-avatar');
    const lastDate = document.getElementById('last-date');
    /**
     * 图片加载
     */
    const highAvatar = document.getElementById('user-avatar-high');
    const lowAvatar = document.getElementById('user-avatar-low');
    // 创建一个新的Image对象
    const img = new Image();
    // 设置高清图片的路径
    img.src = lowAvatar.src + '!o.png';
    // 监听高清图片加载完成事件
    img.onload = function() {
        // 替换模糊图片的src属性为高清图片的路径
        highAvatar.src = img.src;
	    highAvatar.width = lowAvatar.width;
	    highAvatar.height = lowAvatar.height;
        lowAvatar.style.display = 'none';
        highAvatar.style.display = 'block';
    };
    /**
     * 上滑翻页
     */
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
			    loading.style.display = 'flex';
                isLoading = true;
                page += 1;
                // 调用 getChatUsers 函数，并传递一个回调函数
                http_request('POST', '/api/chat/user/' + meUid + '?page=' + page, function (res) {
					console.log('翻页', page, res);
                    isLoading = false;
			        loading.style.display = 'none';
                    if (res.users && res.users.length > 0) {
					    if (res.users.length < 30) {
						    // hasData = false;
                            // document.querySelector('.page').style.display = 'flex';
						}
                        // 遍历数组
                        let html = user_list_html(res.users);
                        if (html) {
						    chatList.insertAdjacentHTML('beforeend', html);
                            //chatList.innerHTML += html;
                        }
                    } else {
                        hasData = false;
                        console.log('没有更多数据了');
                        noMore.style.display = 'flex';
                    }
                });
            }
        }
        // 更新上一次滚动位置
        lastScrollTop = currentScrollTop;
    });

    /**
     * 弹框
     * @type {HTMLElement}
     */
    const warning = document.getElementById('alertWarning');
    const alertSuccess = document.getElementById('alertSuccess');

    /**
     * 刷新记录
     * @type {HTMLElement}
     */
    const btnRefresh = document.getElementById('btn-refresh');
    btnRefresh.addEventListener('click', function () {
        lastDate.innerText = '更新中...';
        page = 1;
        hasData = false;
        btnRefresh.style.display = 'none';
        const refresh_uid = this.getAttribute('data-target');
        noMore.style.display = 'none';
        http_request('POST', '/api/chat/user/' + refresh_uid + '/refresh_chat', function (res) {
			// window.location.reload();
            console.log('刷新', page, res);
            lastDate.innerText = '-- 刚刚更新 --';
            btnRefresh.style.display = 'block';
            if (res.users && res.users.length > 0) {
                hasData = true;
                // location.reload();
                let html = user_list_html(res.users);
                if (html) {
                    chatList.innerHTML = html;
                }
            } else {
                warning.style.display = 'block';
                warning.textContent   = '没有数据';
                setTimeout(function () {
                    warning.style.display = 'none';
                }, 2000);
            }
        });
    });

    /**
     * 关注，取消关注
     * @type {HTMLElement}
     */
    const btnFollow = document.getElementById('btn-follow');
    if (btnFollow) {
        btnFollow.addEventListener('click', function () {
            const uid    = btnFollow.getAttribute('data-uid');
            const content= btnFollow.textContent;
            btnFollow.textContent = content === '关注' ? '取消关注' : '关注';
            http_request('POST', '/api/chat/user/follow/' + uid, function (res) {
                // btnFollow.textContent = content === '关注' ? '取消关注' : '关注';
            });
        });
    }

    /**
     * 修改备注
     * @type {HTMLElement}
     */
    const pencil = document.getElementById('icon-pencil');
    const modal = document.getElementById('myModal');
    const closeModal = document.querySelector('.close');
    const form = document.getElementById("userForm"); // 获取表单元素
    const content = document.getElementById('user-name-content');
    const originalName = content.attributes['data-value'].value;
    pencil.addEventListener('click', function () {
        modal.style.display = 'block';
    });
    // 点击关闭按钮时关闭弹窗
    closeModal.addEventListener('click', () => {
        modal.style.display = 'none';
    });
    // 在用户点击弹窗外部区域时关闭弹窗
    window.addEventListener('touchstart', (event) => {
        if (event.target === modal) {
            modal.style.display = 'none';
        }
    });
    // 提交表单
    form.addEventListener("submit", function(event) {
        event.preventDefault();
        const note = document.getElementById("modal-note").value;
        const uid  = document.getElementById("modal-uid").value;
        const formData = new FormData();
        formData.append("note", note);

        var url = '/api/chat/user/' + uid + '/note/' + note;
        var xhr = new XMLHttpRequest();
        xhr.open('POST', url, true);
        // 添加鉴权头
        xhr.setRequestHeader('Authorization', 'Bearer ' + accessToken);
        // 发送请求
        xhr.send();
        xhr.onreadystatechange = function () {
            if (xhr.readyState == 4) {
                modal.style.display = 'none';
                if (xhr.status == 200) {
                    if (note != "") {
                        content.textContent = originalName + '(' + note + ')';
                    } else {
                        content.textContent = originalName;
                    }
                } else {
                    warning.style.display = 'block';
                    warning.textContent = '网络错误，请稍后再试';
                    setTimeout(function () {
                        warning.style.display = 'none';
                    }, 2000);
                }
            }
        };
    });

    /**
     * 刷新用户信息
     * @type {HTMLElement}
     */
    document.getElementById('user-refresh').addEventListener('click', function () {
        const refresh_uid = this.getAttribute('data-target');
        //http_request('POST', '/api/chat/user/' + refresh_uid + '/refresh_user', function (res) {
            location.reload();
            // let me     = res.me;
            // let others = res.others;
            // // 修改我的信息
            // document.getElementById('user-name-content').textContent = me.name;
        //});
    });
});

function http_request(method, url, callback) {
    // 创建一个请求对象
    const xhr = new XMLHttpRequest();
    xhr.open(method, url, true);
    // 添加鉴权头
    xhr.setRequestHeader('Authorization', 'Bearer ' + accessToken);
    // 设置请求头
    // xhr.setRequestHeader("Content-Type", "application/json");
    // 监听请求完成事件
    xhr.onload = function () {
        if (xhr.status >= 200 && xhr.status < 300) {
            callback(JSON.parse(xhr.response))
        }
    };
    // 监听网络错误事件
    xhr.onerror = function () {
        console.log("Network error occurred");
    };
    // 发送请求
    xhr.send();
}

function user_list_html(users) {
    let html = '';
    for (let k in users) {
        let user = users[k];
        let labelClass = user.chat_count >= 100 ? 'hot' : 'normal';
        let role = user.role >= 0 ? user.role : '';
        let hasImage = user.has_image ? '· 图' : '';
        let isDating = user.is_dating ? '· 约' : '';
		let chatCount = user.chat_count > 0 ? '[' + user.chat_count + ']' : '';
        html += `
        <div class="chat">
            <div class="chat-content">
                <div class="chat-left">
                    <div class="avatar">
                        <a href="/chat/user/` + user.uid + `"><img src="` + user.avatar + `"/></a>
                        <div class="chat-name">
                            <a href='/chat/` + meUid + '/' + user.uid + `'>` + user.name + `
                                <div class="title-basic"><label class="` + labelClass + `">` + chatCount + `</label> ` + user.chat_content +`</div>
                            </a>
                        </div>
                        <div class="time">
                            <div class="time-content">` + user.last_chat_time + `</div>
                        </div>
                    </div>
                </div>
             </div>
        </div>`
    }
    return html;
}
