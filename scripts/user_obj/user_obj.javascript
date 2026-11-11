class JJUser {
    get fullname() {
        return this.firstname + ' ' + this.lastname
    }
    logout() {
        Resource.action('login', 'logout').then(
            result => {
                router$.goto('/')
                toast$.display(result)
                Tag.publish('loggedInUser$', {})
                menu$.reload()
            })
    }
    static getLoggedInUser() {
        return JJUser.loggedInUser
    }
    static initialize() {
        return JJUser.updateLoggedInUser();
    }
    static updateLoggedInUser() {

        let loggedInUser = this.getLoggedInUser()
        if (!loggedInUser) {
            window.loggedInUser$ = {}
            let loggedInUserSession = sessionStorage.loggedInUser
            if (loggedInUserSession) {
                let loggedInUserParsed = JSON.parse(loggedInUserSession)
                let loggedInUser = new JJUser()
                JJUser.loggedInUser = Object.assign(loggedInUser, loggedInUserParsed)
                Tag.publish('loggedInUser$', JJUser.loggedInUser)
                if (window.editor$) {
                    window.editor$.update('this.getRCMenuName')
                }
            }
        }

        return Tag.call('user_obj').then(user => {
            let loggedInUser = new JJUser();
            Object.assign(loggedInUser, user)
            sessionStorage.loggedInUser = JSON.stringify(Object.assign({}, loggedInUser))
            Tag.publish('loggedInUser$', loggedInUser)
            if (window.editor$) {
                window.editor$.update('this.getRCMenuName')
            }
        });
    }
}