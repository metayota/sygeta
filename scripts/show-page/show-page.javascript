class DisplayPage extends Tag {
    setup() {
        this.status = 'loading'
    }
    init() {
      //  this.getPage()
    }

    set page(p) {
        this.getPage()
        this._page = p
    }

    get page() {
        return this._page
    }

    set tab(t) {
        this.getPage()
        this._tab = t
    }

    get tab() {
        return this._tab
    }

    pageLoaded(event) {
        document.title = event.title
        App.pushBreadcrumbs(true)
    }

    getPage() {
        if (this.loadedPage != router$.params.page) {
            this.setAttribute('status','loading')
            this.loadedPage = router$.params.page 
            resource.get('get_page',{page:router$.params.page,usergroup_id:loggedInUser$.usergroup_id}).then(result=> {
                if (result.status == 'not_found') {
                    this.setAttribute('included_page',router$.params.page)
                }
                if (result.resourcetype) {
                    this.setAttribute('resourcetype',result.resourcetype.resourcetype);
                    this.setAttribute('options',result.resourcetype.options);
                }
                this.setAttribute('title_translated',result.title_translated);
                this.setAttribute('show_title',result.show_title);
                this.setAttribute('status',result.status);
                app$.showSubmenu(result.siblings,result.page)
                if (result.page) {
                    menu$.setActivePage(result.page.parent_page ? result.page.parent_page : result.page.name)
                }
                if (result.title_translated) {
                    document.title = result.title_translated
                }

                App.pushBreadcrumbs(true)
            })
        }
    }
}