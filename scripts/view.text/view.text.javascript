class ViewText extends Tag {
    setup() {
        this.defaulttext = ''
    }
    getDisplayValue() {
        if (this.type == 'password') {
            return '*'.repeat(this.value.length)
        }
        return this.value ? this.value : this.defaulttext;
    }

    set name(v) {
        this._name = v
        this.update('this.name')
    }

    get name() {
        return this._name
    }

    set value(v) {
        this._value = v
        this.update('this.getDisplayValue')
    }

    get value() {
        return this._value
    }
}