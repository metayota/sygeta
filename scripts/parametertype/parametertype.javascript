class ParameterType {
    save() {
        return Tag.call('parametertype', { action: 'save', value: JSON.stringify(this) });
    }
    static getTypes() {
        if (!ParameterType.types) {
            ParameterType.types = Resource.getResource('parametertype').data
        }
        return ParameterType.types;
    }
    static loadTypes() {
        return Tag.call('parametertype', { action: 'load' }).then(result => {
            ParameterType.types = result;
            return result;
        });
    }
    static getTypeByName(name) {
        let types = this.getTypes();
        if (types) {
            let idx = types.findIndex(v => { return v.name == name })
            return types[idx]
        } else {
            console.warn('getType. types unresolved') 
        }
    }
}