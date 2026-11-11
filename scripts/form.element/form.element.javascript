class RCFormElement extends Tag {
    setup() {
        this.typesCls = Resource.cls('parametertype') 
        this.types = this.typesCls.types
    }

    render() {
     
    }

    set parent_tag(pt) {
        if (pt) {
            pt['element_'+this.name] = this
        }
    }

    get attributesForElement() {
        let obj = Object.assign( {}, this.options, { name: this.name, label: this.label, value: this.value, validators: this.validators, required: this.required } );
        return obj;
    }
    resourceChanged(v) {
        this.fire('change',v)
    }
    set options(o) {
        this._options = o
        this.update('this.options')
        this.update('this.attributesForElement')
    }
    get options() {
        return this._options
    }
    set parameters(v) {
        this._parameters = v
    }
    get parameters() {
        return this._parameters
    }
    set type(v) {
        this._type = v

        this.resolvedType = this.typesCls.getTypeByName(v)
        
        if (this.resolvedType) {
			// ok
        } else {
            console.warn('form.element type could not be resolved',v)
        }
    } 
    get type() {
        return this._type
    }
    set value(v) {
        this._value = v
        this.update('this.value')
        this.update('this.attributesForElement')
    }
    get value() {
        if (this.include && this.include.renderedTag && this.include.renderedTag.tag) {
            return this.include?.renderedTag?.tag?.value
        } else {
            return this._value
        }
    }
    isValid() {
        if (!this.include.renderedTag.tag.isValid) {
            return true;
        }
        return this.include.renderedTag.tag.isValid()
    }
    set name(n) {
        this._name = n
        this.update('this.name')
        this.update('this.attributesForElement')
    }
    get name() {
        return this._name
    }
    set label(l) {
        this._originalLabel = l
        if (this.required) {
            this._label = l + ' *'
        } else {
            this._label = l
        }
        this.update('this.label')
        this.update('this.attributesForElement')
    }
    get label() {
        return this._label
    }

    set required(r) {
        if (this._required != r) {
            this._required = r
            if (this._originalLabel) {
                if (r) {
                    this._label = this._originalLabel + ' *'
                } else {
                    this._label = this._originalLabel
                }
                this.update('this.attributesForElement')
            }
        }
    }

    get required() {
        return this._required
    }
}