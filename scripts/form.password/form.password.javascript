class Password extends RCBaseFormElement {
    resourceChanged(value) {
        if (this.readonly === true) return
        this._value = value
        this.fire('change', value)
    }

    set errorMessage(em) {
        this._errorMessage = em
        this.update('this.errorMessage')
    }

    get errorMessage() {
        return this._errorMessage
    }
    set value(v) {
        if (v === undefined || v === null) {
            v = ''
        }
        this._value = v
        this.update('this.value')
        if (this.input) {
            this.input.value = v
        }
    }
    get value() {
        return this._value
    }
}