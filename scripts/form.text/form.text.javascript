class FormText extends RCBaseFormElement {

	set label(l) {
		this._label = l
		this.update('this.label')
	}

	get label() {
		return this._label
	}

    set readonly(re) {
        this._readonly = re
    }

    get readonly() {
        return this._readonly
    }

    set autocomplete(a) {
        this._autocomplete = a
        this.update('this.autocomplete')
    }

    get autocomplete() {
        return this._autocomplete
    }

    resourceChanged(value) {
        //if (this.readonly === true) return
	    this._value = value
	    this.fire('change', value)
    }
    
    keypress(e) {
        if (!e) e = window.event;
        var keyCode = e.keyCode || e.which;
        if (keyCode == '13'){
            this.fire('enter','pressed enter')
            return false;
        }
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

    set type(t) {
        this._type = t
        this.update('this.type')
    }

    get type() {
        return this._type
    }
    set placeholder(p) {
        this._placeholder = p
        this.update('this.placeholder')
    }

    get placeholder() {
        return this._placeholder
    }
}