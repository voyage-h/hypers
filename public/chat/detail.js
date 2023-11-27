// 记录上一次滚动位置
var lastScrollTop = 0;
var page = 1;
var meUid;
var meAvatar;
var hasData = true;
var isLoading = false;
const accessToken = `eyJpdiI6InJUdmhibVF2RXRFaE1jZXQ2OFYrYlE9PSIsInZhbHVlIjoiVmtzb05UaTAxN3Nnbncybk9WNjc4S0Y5NDdvcnd0blAvQ2VDL0NJOStyN0xYQnExMFR1amRQYzJtbnpxSGhwUWFPbmRjOW9wdnRPeGNudGNPUUJJU0hnbHdINHd1QnhGZ2R5VS95dU9hRWpBMzdYd3QzTEp0KzFxQlc1QTdZdGsiLCJtYWMiOiJhOTdiMDFlMWM2YjA5MzAxNWFiYjgzY2FjYTBlNDBiZjU4YjVkZWZkMWQxZWFhZTg2NjVhMWY2MzFmMzVhZjIyIiwidGFnIjoiIn0%3D`;

document.addEventListener('DOMContentLoaded', function() {
    const container = document.querySelector('.chat-container');
    meUid = container.getAttribute('data-uid');
    const action = container.getAttribute('data-action');
    const noMore = document.getElementById('page');

    /**
     * 上滑翻页
     */
    window.addEventListener('scroll', function () {
        // 获取当前滚动位置
        var currentScrollTop = window.pageYOffset || document.documentElement.scrollTop;

        // 如果用户向下滑动
        if (currentScrollTop >= lastScrollTop) {
            // 计算页面底部距离
            var distanceToBottom = document.body.offsetHeight - (currentScrollTop + window.innerHeight);

            // 设置一个阈值，比如 20 像素
            var threshold = 100;

            // 如果页面滚动到底部
            if (!isLoading && hasData && distanceToBottom < threshold) {
                loading.style.display = 'flex';
                isLoading = true;
                page += 1;
                // 调用 getChatUsers 函数，并传递一个回调函数
                http_request('POST', '/api/chat/user/' + meUid + '/' + action + '?page=' + page, function (res) {
                    console.log('翻页', page, res);
                    isLoading = false;
                    loading.style.display = 'none';
                    if (Object.keys(res.chats).length > 0) {
                        // 遍历数组
                        //container.innerHTML += detail_html(res);
						container.insertAdjacentHTML('beforeend', detail_html(res));
                        // 需要获取所有图片的尺寸
                        const contentImgs = document.querySelectorAll(".contents-img-a");
                        contentImgs.forEach(function (contentImg) {
                            const image = new Image();
                            const highSrc = contentImg.getAttribute('data-pswp-src');
							image.src = highSrc.replace(/!o\.png$/, '');
                            image.onload = function () {
                                contentImg.setAttribute('data-pswp-width', this.width);
                                contentImg.setAttribute('data-pswp-height', this.height);
                            };
                        });
                    } else {
                        hasData = false;
                        console.log('没有更多数据了');
                        noMore.style.display = 'block';
                    }
                });
            }
        }
        // 更新上一次滚动位置
        lastScrollTop = currentScrollTop;
    });

    // 左屏双击滚动到下一个，右屏双击向上滚动太当前开始
    const chats = document.querySelectorAll(".chat");
    chats.forEach(chat => {
        chat.addEventListener("dblclick", event => {
            const screenWidth = window.innerWidth;
            const clickX = event.clientX;
            if (clickX < screenWidth / 2) {
                const nextChat = chat.nextElementSibling;
                if (nextChat) {
                    nextChat.scrollIntoView({ behavior: "smooth", block: "end" });
                } else {
                    if (alertBox) {
                        alertBox.style.display = "block";
                        alertBox.textContent = "没有更多";
                        setTimeout(function() {
                            alertBox.style.display = "none";
                        }, 1000);
                    }
                }
            } else {
                chat.scrollIntoView({ behavior: "smooth", block: "start" });
            }
        });
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

function detail_html(res) {
    let chats = res.chats;
    let users = res.users;
    let me = res.me;
    let html = '';
    Object.keys(chats).forEach(function(uid) {
        let chat_arr = chats[uid];
        let target_uid = uid == me.uid ? chat_arr[0].target_uid : uid;
        let target = users[target_uid];
        let name = target.name + (target.note ? '(' + target.note.note + ')' : '');
        html += `
    <div class="chat">
        <div class="chat-title">
            <a href="/chat/` + me.uid + `/` + target.uid + `">` + name + `</a>
            <div class="title-basic">` + target.height + `/` + target.weight + `/` + target.role + `</div>
        </div>`;
        let lastDate = 0;
        for (let chat of chat_arr) {
            let contents_html = format_content(chat.contents)
            let chatDate = new Date(chat.created_at);
            let time_html = '';
            if (lastDate === 0 || chatDate - lastDate > 300000) {
                time_html = '<div class="time">' + format_time(chatDate) + '</div>';
            }
            lastDate = chatDate;
            html += `
         <div class="chat-content">
            <div class="chat-` + (chat.from_uid == me.uid ? 'right' : 'left') + `">
                ` + time_html + `
                <div class="avatar">
                    <a href="/chat/user/` + chat.from_uid + `" target="_blank"><img src="` + (chat.from_uid == me.uid ? me.avatar : target.avatar) + `"/></a>
                </div>
                ` + contents_html + `
            </div>
        </div>`;
        }
        html += `</div>`;
    });
    return html;
}

function format_time(time) {
    if (isToday(time)) {
        // 如果是今天，只展示小时
        return time.toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
    } else if (isYesterday(time)) {
        // 如果是昨天，展示昨天和小时
        return '昨天 ' + time.toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
    } else if (isCurrentYear(time)) {
        // 如果是今年，展示月日和小时
        //return time.toLocaleDateString() + ' ' + time.toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
        // 如果是今年，展示月日和小时和分钟
        var month = (time.getMonth() + 1).toString().padStart(2, '0');
        var day = time.getDate().toString().padStart(2, '0');
        var hours = time.getHours().toString().padStart(2, '0');
        var minutes = time.getMinutes().toString().padStart(2, '0');
        return month + '-' + day + ' ' + hours + ':' + minutes;
    } else {
        // 如果是去年，展示年月日
        return time.toLocaleDateString();
    }
}

function isToday(date) {
    var today = new Date();
    return date.getDate() === today.getDate() && date.getMonth() === today.getMonth() && date.getFullYear() === today.getFullYear();
}

function isYesterday(date) {
    var yesterday = new Date();
    yesterday.setDate(yesterday.getDate() - 1);
    return date.getDate() === yesterday.getDate() && date.getMonth() === yesterday.getMonth() && date.getFullYear() === yesterday.getFullYear();
}

function isCurrentYear(date) {
    var today = new Date();
    return date.getFullYear() === today.getFullYear();
}

function format_content(contents) {
    if (contents.startsWith('http')) {
        // 如果是视频
        if (contents.includes('.mp4')) {
            var html = '<div class="contents-video" data-src="' + contents + '">';
            html += '<a class="contents-img-a" href="' + contents + '" data-pswp-src="' + contents + '">';
            html += '<img src="' + contents + '"></a></div>';
            return html;
        }
        // 如果是音频
        else if (contents.includes('.mp3')) {
            return '<div class="contents">[语音]</div>';
        }
        // 如果图片
        else if (contents.match(/\.(jpg|jpeg|png|gif|bmp)$/) || contents.includes('http://dl4')) {
            var imgHtml = '<div class="contents-img">';
            imgHtml += '<a class="contents-img-a" href="' + contents + '!o.png" data-pswp-src="' + contents + '!o.png">';
            imgHtml += '<img src="' + contents + '" /></a></div>';
            return imgHtml;
        }
        else {
            return '<div class="contents"><a href="' + contents + '">' + contents + '</a></div>';
        }
    } else {
        return '<div class="contents">' + (contents.startsWith('RU') ? '[私图]' : contents) + '</div>';
    }
}
