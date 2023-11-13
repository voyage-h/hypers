document.addEventListener('DOMContentLoaded', function() {
// 获取所有图片元素
const contentImgs = document.querySelectorAll(".contents-img");
let currentImg = null;
let isTouchMoved = false; // 新增变量，标记是否发生了滑动

// 遍历每个图片元素并添加触摸事件
contentImgs.forEach(function (contentImg) {
    contentImg.addEventListener('touchstart', function (event) {
        isTouchMoved = false; // 重置滑动标记
        if (currentImg) {
            currentImg.img.remove();
            currentImg.overlay.remove();
        }

        // 创建背景遮罩
        const overlay = document.createElement('div');
        overlay.style.position = 'fixed';
        overlay.style.top = '0';
        overlay.style.left = '0';
        overlay.style.width = '100%';
        overlay.style.height = '100%';
        overlay.style.backgroundColor = 'black'; // 半透明黑色
        overlay.style.zIndex = '9998';

        // 创建新的图片元素
        const img = document.createElement('img');
        img.src = contentImg.getAttribute('data-src') + '!o.png';
        img.style.position = 'fixed';
        img.style.top = '50%';
        img.style.left = '50%';
        img.style.transform = 'translate(-50%, -50%)'; // 居中
        img.style.zIndex = '9999';
        img.style.cursor = 'zoom-out';
        img.style.maxWidth = '100%';
        img.style.maxHeight = '100%';

        // 添加触摸事件，关闭放大的图片
        overlay.addEventListener('touchend', function () {
            if (!isTouchMoved) { // 仅当未发生滑动时才关闭
                img.remove();
                overlay.remove();
                currentImg = null;
            }
        });
        img.addEventListener('touchend', function () {
            if (!isTouchMoved) {
                img.remove();
                overlay.remove();
                currentImg = null;
            }
        });
		

        // 将新的图片元素和背景遮罩添加到 body 中
        document.body.appendChild(overlay);
        document.body.appendChild(img);

        currentImg = { img, overlay };
    });

    contentImg.addEventListener('touchmove', function () {
        isTouchMoved = true; // 标记发生了滑动
    });
});

// 触摸空白处关闭图片
document.addEventListener('touchstart', function (event) {
    if (event.target.tagName.toLowerCase() === 'body' && currentImg) {
        currentImg.img.remove();
        currentImg.overlay.remove();
        currentImg = null;
    }
});

});
