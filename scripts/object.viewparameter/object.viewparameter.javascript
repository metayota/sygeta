class MetayotaObjViewParameter extends Tag {
    set parameter(p) {
        this._parameter = p
        let cls = Resource.cls('parametertype')
        if (cls) {
            let typeInfo = cls.getTypeByName(p.type)
            this.typeInfo = typeInfo
            this.update('this.typeInfo')
        } else {
            console.warn('object.viewparameter did not resolve parameter type class')
        }
        this.update('this.parameter')
    }
    get parameter() {
        return this._parameter
    }
    set value(v) {
        this._value = v
        this.update('this.attributesForElement')
    }
    get value() {
        return this._value
    }
    get attributesForElement() {
        
        let obj = Object.assign( {}, this.parameter.options, { label: this.label, name:this.name, value_translated:this.value_translated, value_resolved:this.value_resolved, value: this.value } );
        return obj;
    }
}