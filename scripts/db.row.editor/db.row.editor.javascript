class JDBRowEditor extends Tag {
    setup() {
        this.showdocumentation = true
    }
    fetchData() {
        if (this.table && (this.rowid || this.row_name)) {
            resource.action('load', { table: this.table, rowid: this.rowid, row_name: this.row_name }).then(value => {
                this.value = value
            })
        }
    }
    set table(t) {
        this._table = t
        this.fetchData()
    }
    get table() {
        return this._table
    }
    set value(v) {
        this._value = v
        this.update('this.value')
    }
    get value() {
        return this._value
    }

    set row_name(row_name) {
        this._row_name = row_name
        this.fetchData()
    }

    get row_name() {
        return this._row_name
    }

    set rowid(rowid) {
        this._rowid = rowid
        this.fetchData()
    }
    get rowid() {
        return this._rowid
    }
    set resourcetype(r) {
        if (r != this._resourcetype) {
            this._resourcetype = r
            if (!this.table) {
                this.table = r
            }
            this.update('this.resourcetype')
            Resource.loaded(r).then(resource => {
                this.resource = resource;
                this.update('this.resource')
                if (!this.title && this.title !== false) {
                    this.setAttribute('title', resource.title)
                }
            })
        }
    }
    get resourcetype() {
        return this._resourcetype
    }
    set label(l) {
        this._label = l
        this.update('this.label')
    }
    get label() {
        return this._label
    }
    save(data) {
        resource.action('save', { data: this.form.value, table: this.table, rowid: this.rowid, row_name: this.row_name }).then(result => {
            if (window.toast$) {
                toast$.display(result)
            } else if (result.message != undefined) {
                alert(result.message)
            }
        })
        this.fire('save', this.form.value)
    }
}