document.addEventListener("DOMContentLoaded", function () {
    const alertBox = document.getElementById("alertWarning");

    // 宸﹀睆鍙屽嚮婊氬姩鍒颁笅涓€涓紝鍙冲睆鍙屽嚮鍚戜笂婊氬姩澶綋鍓嶅紑濮�
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
                        alertBox.textContent = "娌℃湁鏇村";
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

    // 鐢熸垚鏂板唴瀹�
    const newUsers = document.querySelectorAll(".new-user");
    newUsers.forEach(newUser => {
        newUser.addEventListener("click", function() {
            const target = newUser.getAttribute("data-target");
            const confirmed = confirm("鐢熸垚璁板綍锛�");
            if (confirmed) {
                const data = JSON.stringify({
                    target: target,
                    update: 0,
                }); // 璇锋眰鏁版嵁
                performPostRequest1("/chat/create", data, function(res) {
                    if (res.code === 200 && res.message === "ok") {
                        newUser.setAttribute("href", "http://47.110.152.102/" + target + ".html");
                        // 绉婚櫎 .new-user 鍏冪礌鐨� class 灞炴€�
                        newUser.classList.remove("new-user");
                        // 瑙﹀彂鐐瑰嚮鎿嶄綔锛屽鑸埌鏂伴摼鎺�
                        newUser.click();
                    }
                });
            }
        });
    });

    // 鑾峰彇鍒锋柊鎸夐挳鍏冪礌
    const refreshButton = document.getElementById("refreshButton");

    // 娣诲姞鐐瑰嚮浜嬩欢鐩戝惉
    refreshButton.addEventListener("click", function () {
        refreshButton.disabled = true;
        // 闅愯棌鎸夐挳
        refreshButton.style.display = "none";
        const dataTargetValue = refreshButton.getAttribute("data-target");
        // 鎵ц POST 璇锋眰
        const data = JSON.stringify({
            target: dataTargetValue,
            update: 0,
        }); // 璇锋眰鏁版嵁
        performPostRequest("/chat/html", data, function(res) {
            if (res.code === 200 && res.message === "ok") {
                location.reload();
                window.scrollTo(0, 0);
            }
        });
    });

    // 鑾峰彇鎵€闇€鐨勫厓绱�
    const titleMore = document.querySelectorAll('.title-more');
    const modal = document.getElementById('myModal');
    const closeModal = document.querySelector('.close');
    const form = document.getElementById("userForm"); // 鑾峰彇琛ㄥ崟鍏冪礌
    const targetInput = document.getElementById("target");

    let chatTitle
    titleMore.forEach(title => {
        // 鐐瑰嚮 .title-more 鍏冪礌鏃舵樉绀哄脊绐�
        const dataTargetValue = title.getAttribute("data-target");
        title.addEventListener('click', () => {
            // 鑾峰彇褰撳墠 .chat-title 涓嬬殑 a 閾炬帴鐨勬枃瀛�
            chatTitle = title.closest('.chat-title');
            const titleText = chatTitle.querySelector('a').textContent;
            // 璁剧疆寮圭獥涓殑 h2 鏍囬涓洪摼鎺ユ枃瀛�
            const modalTitle = modal.querySelector('h2');
            modalTitle.textContent = titleText;
            form.reset();
            modal.style.display = 'block';
            // 璁剧疆闅愯棌杈撳叆瀛楁鐨勫€间负dataTargetValue
            targetInput.value = dataTargetValue;
        });
    });

    // 鐐瑰嚮鍏抽棴鎸夐挳鏃跺叧闂脊绐�
    closeModal.addEventListener('click', () => {
        modal.style.display = 'none';
    });

    // 鍦ㄧ敤鎴风偣鍑诲脊绐楀閮ㄥ尯鍩熸椂鍏抽棴寮圭獥
    window.addEventListener('touchstart', (event) => {
        if (event.target === modal) {
            modal.style.display = 'none';
        }
    });
    form.addEventListener("submit", function(event) {
        event.preventDefault();
        const target = document.getElementById("target").value;
        const note = document.getElementById("note").value;
        const avatar = document.getElementById("real_avatar").files[0];
        const remove = document.querySelector('input[name="remove_real_avatar"]:checked').value;
        const formData = new FormData();
        formData.append("target", target);
        formData.append("note", note);
        formData.append("real_avatar", avatar);
        formData.append("remove_real_avatar", remove);

        fetch("/chat/update", {
            method: "POST",
            body: formData
        })
            .then(response => response.json())
            .then(data => {
                console.log(data)
                // 澶勭悊鍚庣鍝嶅簲锛屼緥濡傛樉绀烘垚鍔熸秷鎭�
                modal.style.display = 'none'; // 鎻愪氦鍚庡叧闂脊绐�
                if (note) {
                    const originalName = chatTitle.getAttribute("data-name");
                    chatTitle.querySelector('a').textContent = originalName + "(" + note + ")";
                }
                const newAvatarPath = data.data; // 鏇挎崲涓烘柊鐨勫ご鍍忚矾寰�
                if (avatar && newAvatarPath) {
                    // 鑾峰彇褰撳墠.chat涓嬫墍鏈�.chat-left鍏冪礌
                    const chatLeftElements = chatTitle.nextElementSibling.querySelectorAll(".chat .chat-left");
                    // 寰幆閬嶅巻鎵€鏈�.chat-left鍏冪礌骞舵洿鏂板ご鍍�
                    chatLeftElements.forEach(chatLeft => {
                        // 閫氳繃 chat-left 鎵惧埌澶村儚鍥剧墖
                        const avatarImage = chatLeft.querySelector(".avatar img");
                        // 鏇存柊澶村儚鍥剧墖鐨� src 灞炴€�
                        avatarImage.src = newAvatarPath;
                    });
                }
            })
            .catch(error => {
                // 澶勭悊閿欒
                console.error("Error:", error);
            });
    });
});

function performPostRequest(url, data, callback) {
    // 鍒涘缓涓€涓� XMLHttpRequest 瀵硅薄
    const xhr = new XMLHttpRequest();
    // 閰嶇疆 POST 璇锋眰
    xhr.open("POST", "http://47.110.152.102" + url, true);
    // 璁剧疆璇锋眰澶�
    xhr.setRequestHeader("Content-Type", "application/json");
    // 鐩戝惉璇锋眰瀹屾垚浜嬩欢
    xhr.onload = function () {
        if (xhr.status >= 200 && xhr.status < 300) {
            callback(JSON.parse(xhr.response))
        }
    };
    // 鐩戝惉缃戠粶閿欒浜嬩欢
    xhr.onerror = function () {
        console.log("Network error occurred");
    };
    // 鍙戦€� POST 璇锋眰
    xhr.send(data);
}
