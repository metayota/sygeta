class FormLogin extends Tag {
    init() {
        if (document.location.protocol == 'http:') {
            document.location.protocol = 'https';
        }
    }
    logout() {
		resource.action('logout', {}).then( result => {     
            router$.goto('/')
            Tag.publish('loggedInUser$',{})
            menu$.reload()
            window.location = window.location
         })
    }
    login(loginData) {
        loginData.remember_me = true
        resource.call(loginData).then( result => {
            if (result.error != undefined) {
                this.setAttribute('errorMessage',result.error)
                this.loginform.reset();

                let loginRedirectURL = resource.getData().login_redirect_url

                if (loginRedirectURL.startsWith('/')) {
                    router$.goto(loginRedirectURL);
                } else {
                    window.location.href = loginRedirectURL;
                }


            } else {
                Tag.publish('loggedInUser$',Object.assign(new JJUser(), result))
                menu$.reload()
                router$.goto('/login')
                this.setAttribute('errorMessage','')
                window.location = window.location
            }
        })
    }
}