document.addEventListener('DOMContentLoaded', function() {
    const searchBtn = document.getElementById('search-btn');
    const removeBtn = document.getElementById('remove-btn');
    const searchUsers = document.querySelector('.search-users');
    const searchInput = document.getElementById('search-input');
    const warning = document.getElementById('alertWarning');
    const domesticContainer = document.getElementById('domestic-container');
    const delayTime = 500;
    let delayTimer = null;
	searchInput.addEventListener('input', function(){
        clearTimeout(delayTimer);
        delayTimer = setTimeout(function() {
            console.log(searchInput.value.trim());
            const searchValue = searchInput.value.trim();
            // 去掉空格
            if (searchValue === '') {
                searchInput.innerText = '请输入搜索内容';
            } else {
                // searchUsers.innerHTML = '';
                searchBtn.style.display = 'none';
                removeBtn.style.display = 'block';
                http_request('POST', '/api/chat/user/search/' + searchValue, function (res) {
                    if (res.users && res.users.length > 0) {
                        // 遍历数组
                        let html = '';
                        for (let k in res.users) {
                            let user = res.users[k];
                            html += `
    <a href="/chat/user/ ` + user.uid + `">
    <div class="search-user">
        <div class="search-user-avatar">
            <img src="` + user.avatar + `">
        </div>
        <div class="search-user-info">
            <div class="search-user-info-name">` + user.name + `</div>
            <div class="search-user-info-basic">` + user.height + ' ' + user.weight + ' ' + user.role + `</div>
            <div class="search-user-info-time">` + user.last_operate + `</div>
        </div>
    </div>
    </a>`
                        }
                        searchUsers.innerHTML = html;
                    } else {
                        warning.style.display = 'block';
                        warning.textContent   = '没有数据';
                        setTimeout(function () {
                            warning.style.display = 'none';
                        }, 2000);
                    }
					domesticContainer.style.display = 'flex';
                });
            }
        }, delayTime);
    });
	removeBtn.addEventListener('click', function(){
		removeBtn.style.display = 'none';
	    searchBtn.style.display = 'block';
        domesticContainer.style.display = 'none';
		searchInput.value = '';
		searchUsers.innerHTML = '';
		searchInput.setAttribute('placeholder', '搜索');
	});
    domesticContainer.addEventListener('click', function() {
        console.log('search domestic')
        const searchValue = searchInput.value.trim();
        searchBtn.style.display = 'none';
        removeBtn.style.display = 'block';
		domesticContainer.style.display = 'none';
        http_request('POST', '/api/chat/user/search/' + searchValue + '/1', function (res) {
            if (res.users && res.users.length > 0) {
                // 遍历数组
                let html = '';
                for (let k in res.users) {
                    let user = res.users[k];
                    html += `
        <a href="/chat/user/ ` + user.uid + `">
        <div class="search-user">
            <div class="search-user-avatar">
                <img src="` + user.avatar + `">
            </div>
            <div class="search-user-info">
                <div class="search-user-info-name">` + user.name + `</div>
                <div class="search-user-info-basic">` + user.height + ' ' + user.weight + ' ' + user.role + `</div>
                <div class="search-user-info-time">` + user.last_operate + `</div>
            </div>
        </div>
        </a>`
                }
                searchUsers.innerHTML = html;
            } else {
                warning.style.display = 'block';
                warning.textContent   = '没有数据';
                setTimeout(function () {
                    warning.style.display = 'none';
                }, 2000);
            }

        });
    });
});

function http_request(method, url, callback) {
    // 创建一个请求对象
    const xhr = new XMLHttpRequest();
    xhr.open(method, url, true);
    // 添加鉴权头
    const accessToken = `eyJpdiI6InJUdmhibVF2RXRFaE1jZXQ2OFYrYlE9PSIsInZhbHVlIjoiVmtzb05UaTAxN3Nnbncybk9WNjc4S0Y5NDdvcnd0blAvQ2VDL0NJOStyN0xYQnExMFR1amRQYzJtbnpxSGhwUWFPbmRjOW9wdnRPeGNudGNPUUJJU0hnbHdINHd1QnhGZ2R5VS95dU9hRWpBMzdYd3QzTEp0KzFxQlc1QTdZdGsiLCJtYWMiOiJhOTdiMDFlMWM2YjA5MzAxNWFiYjgzY2FjYTBlNDBiZjU4YjVkZWZkMWQxZWFhZTg2NjVhMWY2MzFmMzVhZjIyIiwidGFnIjoiIn0%3D`;
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
