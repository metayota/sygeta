class RCResourceWithParams extends Tag {
    setup() {
        console.log('Created RCResourceWithParams')
        resource.action('list_resources').then(result=> {
            this.setAttribute('resourceTypes',result)
        })
    }
    set resourcetype(t) {
        this._resourcetype = t
        this.update('this.resourcetype')
    }
    get resourcetype() {
        return this._resourcetype
    }
    set options(o) {
        this._options = o
        this.update('this.options')
    }
    get options() {
        return this._options
    }
    resourceOptionsChanged(o) {
        this._options = o
        this.resourceChanged()
    }
    resourceTypeChanged(v) {
        this.setAttribute('resourcetype',v)
        this.fire('change',this.value)
    }
    resourceChanged() {
        this.fire('change',this.value)
    }
    set value(v) {
        if (v && (typeof v == 'string')) {
            v = JSON.parse(v)
        }
        if (v!=undefined) {
            this.setAttribute('options',v.options)
            this.setAttribute('resourcetype',v.resourcetype)
        }
    }
    get value() {
        return {options:this.options, resourcetype:this.resourcetype}
    }
}