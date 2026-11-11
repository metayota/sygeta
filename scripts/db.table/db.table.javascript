class JDBTable extends Tag {
    set where(w) {
        this._where = w
        this.reloadData()
    }
    get where() {
        if (!this._where) {
            this._where = []
        }
        return this._where
    }
    set table(t) {
        this._table = t
        this.reloadData()
    }
    get table() {
        return this._table
    }
    reloadData() {
        if (this.table && this.resourcetype) {
            let where = {}
            for (let whereItem of this.where) {
                where[whereItem.field] = whereItem.value
            }
            resource.call({table:this.table,where:where, resourcetype:this.resourcetype}).then(result => {
                this.objs = result 
                this.update('this.objs')
            })
        }
    }
    set resourcetype(r) {
        if (this._resourcetype !== r) {
            this._resourcetype = r
            Resource.loaded(r).then( resource => {
                this.resource = resource
                this.update('this.resource')
                this.update('this.resourcetype')
            })
        }
        this.reloadData()
    }
    get resourcetype() {
        return this._resourcetype
    }

}