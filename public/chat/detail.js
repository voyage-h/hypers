document.addEventListener('DOMContentLoaded', function() {
    const contentImgs = document.querySelectorAll(".contents-img");
    let currentImg = null;
    let touchStartX = 0;
    let touchStartY = 0;
    const touchMoveThreshold = 10; // 调整滑动判定阈值
    const clickThreshold = 5; // 调整点击判定阈值

    contentImgs.forEach(function (contentImg) {
        contentImg.addEventListener('touchstart', function (event) {
            touchStartX = event.touches[0].clientX;
            touchStartY = event.touches[0].clientY;
        });

        contentImg.addEventListener('touchend', function (event) {
            const touchEndX = event.changedTouches[0].clientX;
            const touchEndY = event.changedTouches[0].clientY;

            if (Math.abs(touchEndX - touchStartX) < clickThreshold && Math.abs(touchEndY - touchStartY) < clickThreshold) {
                // 点击图片，执行放大操作
                if (currentImg) {
                    currentImg.img.remove();
                    currentImg.overlay.remove();
                }

                const overlay = document.createElement('div');
                overlay.style.position = 'fixed';
                overlay.style.top = '0';
                overlay.style.left = '0';
                overlay.style.width = '100%';
                overlay.style.height = '100%';
                overlay.style.backgroundColor = 'black';
                overlay.style.zIndex = '9998';

                const img = document.createElement('img');
                img.src = contentImg.getAttribute('data-src') + '!o.png';
                img.style.position = 'fixed';
                img.style.top = '50%';
                img.style.left = '50%';
                img.style.transform = 'translate(-50%, -50%)';
                img.style.zIndex = '9999';
                img.style.cursor = 'zoom-out';
                img.style.maxWidth = '100%';
                img.style.maxHeight = '100%';

                overlay.addEventListener('touchend', function () {
                    img.remove();
                    overlay.remove();
                    currentImg = null;
                });

                img.addEventListener('touchend', function () {
                    img.remove();
                    overlay.remove();
                    currentImg = null;
                });

                document.body.appendChild(overlay);
                document.body.appendChild(img);

                currentImg = { img, overlay };
            }
        });

        contentImg.addEventListener('touchmove', function (event) {
            const touchMoveX = event.touches[0].clientX;
            const touchMoveY = event.touches[0].clientY;

            if (Math.abs(touchMoveX - touchStartX) > touchMoveThreshold || Math.abs(touchMoveY - touchStartY) > touchMoveThreshold) {
                // 滑动距离超过阈值，取消放大操作
                currentImg = null;
            }
        });
    });

    document.addEventListener('touchstart', function (event) {
        if (event.target.tagName.toLowerCase() === 'body' && currentImg) {
            currentImg.img.remove();
            currentImg.overlay.remove();
            currentImg = null;
        }
    });
});

