class RadioGroup extends RCBaseFormElement {
    set value(v) {
        this._value = v
        this.update('this.value')
    }

    get value() {
        return this._value
    }

    set label(l) {
        this._label = l
        this.update('this.label')
    }

    get label() {
        return this._label
    }

    set options(o) {
        if (typeof o ==  'string' && o != '') {
            o = JSON.parse(o)
        }
        this._options = o
        this.update('this.options')
    }
    get options() {
        return this._options
    }
    select(value) {
        this.value = value
        this.update('this.options')
        this.fire('change', value);
    }

}