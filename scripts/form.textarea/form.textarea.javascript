class FormTextarea extends RCBaseFormElement {
    handleInput(node) {
        this.value = node.value
        this.fire('change',this.value)
    }
    
    set value(val) {
        this._value = val;
        this.update('this.value')
    }
    
    get value() {
        return this._value
    }
}