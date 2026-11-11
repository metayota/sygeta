class ViewOption extends Tag {
    set value(v) {
        this._value = v 
        let option = this.options.find( o => o.value == v)
        if (option) {
            this.setAttribute('displayValue',option.name)
        }
    }
    get value() {
        return this._value
    }
}