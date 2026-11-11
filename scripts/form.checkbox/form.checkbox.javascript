class Checkbox extends RCBaseFormElement {

	setup() {
		this._value = false
	}

	set value(v) {
		this._value = (v == true) || (v == 'true') || (v == 1) || (v == '1')
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

	set htmllabel(l) {
		this._htmllabel = l
	}

	get htmllabel() {
		return this._htmllabel;
	}

	render() {
		if (this.htmllabel) {
			this.labelel.innerHTML = translate(this.htmllabel)
		}
	}

	check() {
        if (!this.value) {
            this.value = true
        } else {
            this.value = false
        }
		this.fire('change',this.value);
	}
    keydown(event) {
        if (event.keyCode == 32 || event.keyCode == 13) {
            this.check()
            event.preventDefault();
            event.stopPropagation();
        }
 
    }
    
}