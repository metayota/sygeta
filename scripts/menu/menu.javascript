class Menu extends Tag {
    init() {
        this.reload()
        this.publish('menu$',this)
    }

    reload() {
        resource.get('get_menu',{usergroup_id:loggedInUser$.usergroup_id,language:translate('current_language')}).then(result=> {
            this.setAttribute('menu',result)
        })
    }

    setActivePage(pageName) {
        this.setAttribute('activePage',pageName)
        this.update('this.menu')
    }

    clickedItem(menuItem) {
        app$.breadcrumbs = []
        if (menuItem && menuItem.url && menuItem.url != '') {
            window.location = menuItem.url
        } else {
            router$.goto('/'+menuItem.name)
        }
        App.pushBreadcrumbs(true)
    }
}