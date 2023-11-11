document.addEventListener("DOMContentLoaded", function () {
    // 获取刷新按钮元素
    const refreshButton = document.getElementById("refreshButton");
    // 添加点击事件监听
    refreshButton.addEventListener("click", function () {
        refreshButton.disabled = true;
        // 隐藏按钮
        refreshButton.style.display = "none";
        const dataTargetValue = refreshButton.getAttribute("data-target");
        // 执行 POST 请求
        const data = JSON.stringify({
            target: dataTargetValue,
            update: 0,
        }); // 请求数据
        performPostRequest("/chat/user/" + dataTargetValue + "/refresh", data, function (res) {
            if (res.code === 200 && res.message === "ok") {
                location.reload();
                window.scrollTo(0, 0);
            }
        });
    });
});

function performPostRequest(url, data, callback) {
    // 创建一个 XMLHttpRequest 对象
    const xhr = new XMLHttpRequest();
    // 配置 POST 请求
    xhr.open("POST",  url, true);
    // 设置请求头
    xhr.setRequestHeader("Content-Type", "application/json");
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
    // 发送 POST 请求
    xhr.send(data);
}
