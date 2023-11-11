document.addEventListener("DOMContentLoaded", function () {
    const alertBox = document.getElementById("alertWarning");

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

	// 生成新内容
    const newUsers = document.querySelectorAll(".new-user");
    newUsers.forEach(newUser => {
        newUser.addEventListener("click", function() {
            const target = newUser.getAttribute("data-target");
            const confirmed = confirm("生成记录？");
            if (confirmed) {
                const data = JSON.stringify({
                    target: target,
                    update: 0,
                }); // 请求数据
               performPostRequest1("/chat/create", data, function(res) {
					if (res.code === 200 && res.message === "ok") {
                        newUser.setAttribute("href", "http://47.110.152.102/" + target + ".html");
						// 移除 .new-user 元素的 class 属性
		                newUser.classList.remove("new-user");
                        // 触发点击操作，导航到新链接
                        newUser.click();
					}
			   });
            }
        });
    });	

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
       performPostRequest("/chat/html", data, function(res) {
			if (res.code === 200 && res.message === "ok") {
	            location.reload();		
				window.scrollTo(0, 0);
			}
	   });
    });

    // 获取所需的元素
    const titleMore = document.querySelectorAll('.title-more');
    const modal = document.getElementById('myModal');
    const closeModal = document.querySelector('.close');
    const form = document.getElementById("userForm"); // 获取表单元素
    const targetInput = document.getElementById("target");

	let chatTitle
	titleMore.forEach(title => {
        // 点击 .title-more 元素时显示弹窗
		const dataTargetValue = title.getAttribute("data-target"); 
        title.addEventListener('click', () => {
            // 获取当前 .chat-title 下的 a 链接的文字
            chatTitle = title.closest('.chat-title');
            const titleText = chatTitle.querySelector('a').textContent;		  
            // 设置弹窗中的 h2 标题为链接文字
            const modalTitle = modal.querySelector('h2');
            modalTitle.textContent = titleText;
            form.reset();			
            modal.style.display = 'block';
           // 设置隐藏输入字段的值为dataTargetValue
           targetInput.value = dataTargetValue;
        });
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
          // 处理后端响应，例如显示成功消息
          modal.style.display = 'none'; // 提交后关闭弹窗
          if (note) {
              const originalName = chatTitle.getAttribute("data-name");
              chatTitle.querySelector('a').textContent = originalName + "(" + note + ")";
          }
		  const newAvatarPath = data.data; // 替换为新的头像路径 
          if (avatar && newAvatarPath) {
              // 获取当前.chat下所有.chat-left元素
              const chatLeftElements = chatTitle.nextElementSibling.querySelectorAll(".chat .chat-left");
              // 循环遍历所有.chat-left元素并更新头像
              chatLeftElements.forEach(chatLeft => {
                  // 通过 chat-left 找到头像图片
                  const avatarImage = chatLeft.querySelector(".avatar img");
                  // 更新头像图片的 src 属性
                  avatarImage.src = newAvatarPath;
              });			  
          }
        })
        .catch(error => {
          // 处理错误
          console.error("Error:", error);
        });
    });
});

function performPostRequest(url, data, callback) {
    // 创建一个 XMLHttpRequest 对象
    const xhr = new XMLHttpRequest();
    // 配置 POST 请求
    xhr.open("POST", "http://47.110.152.102" + url, true);
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
