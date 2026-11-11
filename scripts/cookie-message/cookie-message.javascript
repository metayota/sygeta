class CookieMessage extends Tag {
    init() {
        if (this.checkCookie('allow_cookies') !== 'true') {
            toast$.showCallback('allow_cookies_title', 'allow_cookies_message', function () {
                this.setCookie('allow_cookies', 'true')
                Tag.publish('allowCookies$', true)
                app$.initAnalytics()
            }.bind(this), 'allow_cookies_button', 'success')
            Tag.publish('allowCookies$', false)
        } else {
            Tag.publish('allowCookies$', true)
        }
    }

    checkCookie(cookieName) {
        let cookieValue = document.cookie
            .split('; ')
            .find(row => row.startsWith(cookieName))
            ?.split('=')[1];
        return cookieValue;
    }

    setCookie(cookieName, cookieValue, expirationDays) {
        let date = new Date();
        date.setTime(date.getTime() + (expirationDays * 24 * 60 * 60 * 1000));
        let expires = "; expires=" + date.toUTCString();
        document.cookie = cookieName + "=" + (cookieValue || "") + expires + "; path=/";
    }
}