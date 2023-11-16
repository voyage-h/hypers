document.addEventListener('DOMContentLoaded', function() {
    const contentImgs = document.querySelectorAll(".contents-img-a");
    contentImgs.forEach(function (contentImg) {
        const image = new Image();
        image.src = contentImg.getAttribute('data-pswp-src');
        image.onload = function () {
            contentImg.setAttribute('data-pswp-width', this.width);
            contentImg.setAttribute('data-pswp-height', this.height);
        };
    });
});

