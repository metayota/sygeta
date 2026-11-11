class ResourceFormTag extends Tag {
    setup() {
        this.autocomplete = false
        this.showdocumentation = true
    }
    init() {
        this._enabled = true
    }

    set readonly(r) {
        if (this._readonly != r) {
            this._readonly = r
            this.update('this.readonly')
        }
    }

    get readonly() {
        return this._readonly
    }

    set hide_parameters(h) {
        this._hide_parameters = h
        this.update('this.hide_parameters')
    }
    get hide_parameters() {
        return this._hide_parameters
    }
    set target(t) {
        this._target = t
        this.update('this.target')
    }
    get target() {
        return this._target
    }
    set showdocumentation(s) {
        this._showdocumentation = s
        this.update('this.showdocumentation')
    }
    get showdocumentation() {
        return this._showdocumentation
    }
    set action(a) {
        this._action = a
        this.update('this.action')
    }
    get action() {
        return this._action
    }
    set method(m) {
        this._method = m
        this.update('this.method')
    }
    get method() {
        return this._method
    }
    set label(l) {
        this._label = l
        this.update('this.label')
    }
    get label() {
        return this._label
    }
    set enabled(e) {
        this._enabled = e
    }
    get enabled() {
        return this._enabled
    }
    set documentation(d) {
        this._documentation = d
        this.update('this.documentation')
    }
    get documentation() {
        return this._documentation
    }
    set subtitle(s) {
        this._subtitle = s
        this.update('this.subtitle')
    }
    isValid() {
        let isValid = this.parameterEl.isValid()
        if (!isValid) {
            toast$.display({error:translate('form_contains_errors_please_correct')})
        }
        return isValid
    }
    get subtitle() {
        return this._subtitle
    }
    set submitlabel(l) {
        this._submitlabel = l
        this.update('this.submitlabel')
    }
    get submitlabel() {
        return this._submitlabel
    }
    set cancellabel(c) {
        this._cancellabel = c
        this.update('this.cancellabel')
    }
    get cancellabel() {
        return this._cancellabel
    }
    set resource_name(r) {
        this.resourcetype = r
    }
    get resourcetype() {
        return this.resourcetype
    }
    /*use of resorucetype is deprecated*/
    set resourcetype(r) {
        if (this._resourcetype != r) {
            this._resourcetype = r
            if (r) {
                Resource.loaded(r).then(resource => {
                    this.resource = resource;
                    this.update('this.resource')
                    this.update('this.resourcetype')
                    let obj = Resource.createWithDefaults(this._resourcetype)
                    if (!this.value) {
                        this.value = obj
                    } else {
                        this.value = Object.assign({}, obj, this.value)
                    }
                })
            } else {
                this.resource = null
                this.update('this.resource')
                this.update('this.resourcetype')
            }
        }
    }
    get resourcetype() {
        return this._resourcetype
    }
    set value(v) {
        this._value = v
        this.update('this.value')
    }
    get value() {
        return this._value
    }
    clear() {
        this.value = {}
    }
    reset() {
        this.value = {}
    }
    changed(event) {
        this.setAttribute('submitted', false)
        this._value = event;
        this.fire('change', event)
    }
    cancelClicked() {
        this.value = {}
        this.fire('cancel')
    }
    performSave(res) {
        this.parameterEl.save()
        let obj = Resource.createWithDefaults(this._resourcetype)
        Object.assign(obj, this.parameterEl.value)

        if (this.filter_value === true) {
            let newObj = {}
            res.parameters.forEach(param => {
                if (obj.hasOwnProperty(param.name)) {
                    newObj[param.name] = obj[param.name] === undefined ? null : obj[param.name];
                } else {
                    newObj[param.name] = null;
                }
            })
            obj = newObj
        }

        this.fire('submit', obj)
        if (!!this.action) {
            this.form.submit()
        }
    }
    saveClicked() {
        if (!this.enabled || (this.submitted === true)) {
            return;
        }
        //this.setAttribute('submitted', true)
        Resource.loaded(this._resourcetype).then(res => {
            let isValid = this.isValid()
            if (isValid instanceof Promise) {
                this.setAttribute('showLoader', true)
                isValid.then(result=> {
                    this.performSave(res)
                    this.setAttribute('showLoader', false)
                })
            } else if(isValid) {
                this.performSave(res)
            }
        })
    }

}