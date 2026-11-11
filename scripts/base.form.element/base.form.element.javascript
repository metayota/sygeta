class RCBaseFormElement extends Tag {
    isValid() {
        if (this.validators) {
            for (let validator of this.validators) {
                let validatorObj = Resource.create(validator.name);
                Object.assign(validatorObj,validator.options)
                if (this.value == null) {
                    this.value = ''
                }
                let validatorResult = validatorObj.isValid(this.value)
                if (validatorResult !== true) {
                    this.errorMessage = translate(validatorResult)
                    this.update('this.errorMessage')
                    return false
                }
            }
        }
        if (this.required && !this.readonly && (!this.value || this.value == '')) {
            
            this.errorMessage = translate('fill_out_form_field')
            this.update('this.errorMessage')
            return false
        }
        if (this.errorMessage) {
            this.errorMessage = undefined
            this.update('this.errorMessage')
        }
        return true
    }
}