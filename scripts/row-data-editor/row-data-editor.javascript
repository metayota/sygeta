class RowDataEdit extends Tag {

    set rowid(r) {
        if (this._rowid != r) {
            this._rowid = r
            this.update('this.rowid')
        }
    }

    get rowid() {
        return this._rowid
    }

    set resourcetype(rt) {
        if (this._resourcetype != rt) {
            this._resourcetype = rt
            resource.action('get_texts', {'resourcetype':this.resourcetype}).then(result=>{
                if (result) {
                    this.setAttribute('title', result.title)
                    this.setAttribute('description', result.description)
                    document.title = result.title
                    App.pushBreadcrumbs(true)
                }
            })
            this.update('this.resourcetype')
        }
    }

    get resourcetype() {
        return this._resourcetype
    }
}