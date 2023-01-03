var topline_close = document.querySelector('#top-line .close-topline');
var topline = document.querySelector('#top-line');

if(topline_close) {
    topline_close.addEventListener('click', function (e) {
        var id = topline_close.getAttribute('data-cookie-id');
        function setcookie(cookieName,cookieValue) {
            var today = new Date();
            var expire = new Date();
            expire.setTime(today.getTime() + 3600000*24);
            document.cookie = cookieName+"="+encodeURI(cookieValue) + ";expires="+expire.toGMTString();
        }

        setcookie(id, "closed");
        topline.remove();

        // event
        var event = new CustomEvent('mdn/topline', {status: "close"});
        document.dispatchEvent(event);
    })
}

