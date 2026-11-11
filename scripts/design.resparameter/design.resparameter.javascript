class ResParameter extends Tag {

    setup() {
        this._value = {}
        this.parent_reference = this
    }

    set hide_parameters(h) {
        this._hide_parameters = h
        this.update('this.isHidden')
    }

    get hide_parameters() {
        return this._hide_parameters
    }


    set readonly(r) {
        if (this._readonly != r) {
            this._readonly = r
            this.update('this.parameters')
            this.setAttribute('parent_reference', this)
        }
    }

    get readonly() {
        return this._readonly
    }

    isHidden(p) {
        return p.hidden || (this.hide_parameters && this.hide_parameters.includes(p.name))
    }

    set elements(e) {
        this._elements = e
    }

    get elements() {
        return this._elements
    }

    set showdocumentation(s) {
        this._showdocumentation = s
        this.update('this.showdocumentation')
    }

    get showdocumentation() {
        return this._showdocumentation
    }

    set documentation(d) {
        this._documentation = d
        this.update('this.documentation')
    }


    get documentation() {
        return this._documentation
    }

    set tag(tag) {
        this._tag = tag;
        this._elements = []
        this.parameters = this.getParameters()
        this._elements = [] //they are going to register again
        //if (tag && this._value) {
            this.update('this.parameters')
           //    this.update('parameter')
        //}
        this.setAttribute('parent_reference', this)
    }

    get tag() {
        return this._tag;
    }

    parameterChanged(parameterName, value) {
        if (this._value === undefined || this._value === null) {
            this._value = {}
        }
        this._value[parameterName] = value
        this.fire('change', this.value)
    }

    save() {
        let elements = this.getElements()
        for (let element of elements) {
            if (element.include?.renderedTag?.ownsTag?.save) {
                element.include?.renderedTag?.ownsTag?.save()
            }
        }
    }

    getElements() {
        let elements = []
        for(let parameter of this.parameters) {
            let foundEl = this['element_'+parameter.name]
            if (foundEl) {
                elements.push(foundEl)
            } else {
                console.log('not found element',parameter.name)
            }
        }
        return elements
    }

    isValid() {
        let valid = true
        let elements = this.getElements()
        let promises = []
        for (let element of elements) {
            if (!this.paramIsHiddenOrReadonly(element.name)) {
                if (element.isValid) {
                    let elementIsValid = element.isValid()
                    if (elementIsValid == false) {
                        valid = false
                        console.log("element " + element.name + " is not valid")
                    } 
                    if (elementIsValid instanceof Promise) {
                        promises.push(elementIsValid)
                    }
                }
            }
        }
        if (promises.length == 0) {
            return valid
        } else {
            return Promise.all(promises)
        }
    }

    getParams(tag) {
        if (!tag) {
            return []
        }
        let params = []
            
        try {
            if (!tag.parameters) {
                params = []
            } else if (typeof tag.parameters != 'object') {
                params = JSON.parse(this._tag.parameters)
            } else {
                params = tag.parameters
            }
        } catch (e) {
            console.warn(e)
        }
        let value = this._value ? this._value : {}
        let paramsAndValues = []
        if (params) {
            for (let param of params) {
                let parameter = Object.assign({}, param)
                if (value[param.name] !== undefined) {
                    parameter.value = value[param.name]
                }
                paramsAndValues.push(parameter)
            }
            paramsAndValues.formElement = this
        }
        /* if (tag.extends_resource != undefined) {
             let extendsRes = Resource.getResource(tag.extends_resource)
             if (extendsRes != undefined) {
                 paramsAndValues.push(this.getParams(extendsRes)) 
 
             }
         }*/
        return paramsAndValues
    }

    getParameters() {
        return this.getParams(this._tag)
    }

    getParamByName(name) {
        return this.parameters.find( (p) => (p.name == name) )
    }

    paramIsHiddenOrReadonly(name) {
        let parameter = this.getParamByName(name)
        return ((this.readonly === true || parameter.readonly === true) && !(parameter.required && !parameter.value)) || this.isHidden(parameter);
    }

    set value(v) {
        if (v == '' || v == null) {
            v = {}
        }
        if (this._value == v) {
            return
        }
        if (Object.keys(this._value).length === 0 && Object.keys(v).length === 0) {
            return
        }
        this._value = v;
        this.parameters = this.getParameters()
        this.update('this.value')
        
      
        this.update('this.parameters')
       this.update('parameter.value')
        //this.update('parameter')
        this.setAttribute('parent_reference', this)
    }

    get value() {
        let elements = this.getElements()
        if (elements && elements.length > 0) {
            let existingValue = this._value ? this._value : {}

            // Start with ALL existing values (shallow copy to avoid mutation)
            let associativeObject = { ...existingValue };

            // Now process only the elements to update or delete
            elements.forEach(item => {
                if (!item.include?.renderedTag?.ownsTag?.getSaveValue || item.include?.renderedTag?.ownsTag?.getSaveValue() !== false) {
                    // Update/add the value
                    
                    if (!this.paramIsHiddenOrReadonly(item.name)) {
                        associativeObject[item.name] = item.value;
                    }
                } else {
                    // getSaveValue() explicitly returned false, so delete this entry
                    delete associativeObject[item.name];
                }
            });

            return associativeObject
        }

        return this._value;
    }
}