document.addEventListener('DOMContentLoaded', function() {
    // 警告
    const warning = document.getElementById('alertWarning');

    // 点击图片放大
    const contentImg = document.querySelector(".contents-img");
    contentImg.addEventListener('click', function () {
        const img = document.createElement('img');
        img.src = contentImg.getAttribute('data-src');
        img.style.width = '100%';
        img.style.height = 'auto';
        img.style.position = 'fixed';
        img.style.top = '0';
        img.style.left = '0';
        img.style.zIndex = '9999';
        img.style.cursor = 'zoom-out';
        img.addEventListener('click', function () {
            img.remove();
        });
        document.body.appendChild(img);

    });
});
