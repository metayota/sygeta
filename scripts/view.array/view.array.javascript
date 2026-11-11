class JViewArray extends Tag {
    setup() {
        this._value = []
    }
    set value(v) {
        if (v) {
            this._value = v
        } else {
            this._value = []
        }
        this.update('this.value')
    }
    get value() {
        return this._value
    }
    set resourcetype(r) {
        this._resourcetype = r
        Resource.loaded(r).then( resource => {
            this.resource = resource
            this.update('this.resource')
            this.update('this.value')
        })
        this.update('this.resourcetype')
    }
    get resourcetype() {
        return this._resourcetype
    }
    
}