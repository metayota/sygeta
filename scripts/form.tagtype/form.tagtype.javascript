class JJFormTagtype extends RCBaseFormElement {
    setup() {
        this.label = 'Resource Type'
        resource.call({}).then( tags => {
            this.tags = tags.map((v, i) => { return { name: v.title_translated ? v.title_translated : v.name, value: v.name, "type": v.type, 'extends_resource' : v.extends_resource } });
            this.update('this.tags')
            this.filterTags()
        });
    }
    set value(v) {
        this._value = v
        this.update('this.value')
    }
    get value() {
        return this._value
    }
    updated(event) {
        this._value = event
        this.fire('change',event)
    }
    filterTags() {
        if (this.tags) {
            
            if (this.typefilter) {
                this.filteredTags = this.tags.filter(tag => {
                    return tag.type == this.typefilter
                })
            } else {
                this.filteredTags = this.tags
            }
            if (this.resource_type) {
                this.filteredTags = this.filteredTags.filter(tag => {
                    return tag.extends_resource == this.resource_type
                })
            }
            this.update('this.filteredTags')
        }
    }
    set typefilter(t) {
        this._typefilter = t
        this.filterTags()
    }
    get typefilter() {
        return this._typefilter
    }
    set label(l) {
        this._label = l
        this.update('this.label')
    }
    get label() {
        return this._label
    }
}