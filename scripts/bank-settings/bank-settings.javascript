class ChoosePaymentMethod extends Tag {
    init() {
        resource.action('get_data').then(result=> {
            this.paymentMethods = result.paymentMethods
            let paymentMethodOptions = this.paymentMethods.map(method => ({
                name: method.title_translated,
                value: method.name
            }))
            this.setAttribute('paymentMethodOptions', paymentMethodOptions)
            this.setAttribute('paymentMethods', result.paymentMethods)
            this.setAttribute('paymentMethod', result.paymentMethod)
            this.setAttribute('companyPaymentMethod', result.companyPaymentMethod)
        })
    }

    paymentMethodChosen(event) {
        this.companyPaymentMethod.payment_method = event
        this.companyPaymentMethod.payment_method_data = {}
        this.update('this.companyPaymentMethod')
        this.setAttribute('paymentMethod',  this.paymentMethods.find( v => v.name == event))
    }

    save(payment_method_data) {
        resource.action('save', {payment_method:this.paymentMethod.name, payment_method_data}).then(result=> {
            toast$.display(result)
        })
    }
}