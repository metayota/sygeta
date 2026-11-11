class App extends Tag {

    setup() {
        this.publish('app$', this)
        this.initUser()

        window.formatNumber = function formatNumber(number, decimalPlaces = 0) {
            let groupSize = 3
            let decimalSeparator = translate('number_format_decimal_separator')
            let digitGroupSeparator = translate('number_format_group_separator')
            // Zunächst die Dezimalstellen formatieren
            number = number * 1
            number = number.toFixed(decimalPlaces);

            // Dann Tausendergruppen erstellen
            let parts = number.toString().split(".");

            parts[0] = parts[0].replace(new RegExp(`\\B(?=(\\d{${groupSize}})+(?!\\d))`, 'g'), digitGroupSeparator);

            return parts.join(decimalSeparator);
        }
        this.loadTasks()
        app$.breadcrumbs = []
    }

    static pushBreadcrumbs(replace = false) {
        const currentBreadcrumb = {
            url: window.location.pathname.substring(3), // or however you get the current URL
            title: document.title // or however you get the current title
        }

        // Check if URL already exists in breadcrumbs
        const existingIndex = app$.breadcrumbs.findIndex(breadcrumb => breadcrumb.url === currentBreadcrumb.url)

        if (existingIndex !== -1) {
            if (replace) {
                // Replace the existing breadcrumb (maybe different title)
                app$.breadcrumbs[existingIndex] = currentBreadcrumb
            } 
                // URL exists - remove all breadcrumbs after this one
                app$.breadcrumbs = app$.breadcrumbs.slice(0, existingIndex + 1)
            
        } else {
            // URL doesn't exist - push to array
            app$.breadcrumbs.push(currentBreadcrumb)
        }
        
        notify('app$.breadcrumbs')
    }

    loadTasks() {
        resource.action('misc_tasks').then(result => {
            toast$.display(result)
        })
    }

    render() {
        window.mainScrollEl = this.mainEl
    }

    initUser() {
        let user = Resource.cls('user_obj')
        if (user) {
            user.updateLoggedInUser();
        } else {
            console.warn('user is not defined')
        }
    }

    gotoSubmenu(menuItem) {
        app$.breadcrumbs = []
        router$.goto('/'+menuItem.name)
    }

    showSubmenu(menu, page) {
        this.setAttribute('currentPage', page)
        this.setAttribute('menu', menu)
       
    }
}