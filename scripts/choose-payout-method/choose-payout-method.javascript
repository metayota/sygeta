class ChoosePayoutMethod extends Tag {
    init() {
        resource.action('get_data').then(result=> {
       
            this.payoutMethods = result.payoutMethods
            let payoutMethodOptions = this.payoutMethods.map(method => ({
                name: method.title_translated,
                value: method.id
            }))
            this.setAttribute('payoutMethodOptions', payoutMethodOptions)
            this.setAttribute('payoutMethod', result.payoutMethod)
            this.setAttribute('partnerPayoutMethod', result.partnerPayoutMethod)
        })
    }

    payoutMethodChosen(event) {
        this.setAttribute('payoutMethod',  this.payoutMethods.find( v => v.id == event))
    }

    save(payout_data) {
        resource.action('save', {payout_method_id:this.payoutMethod.id, payout_data}).then(result=> {
            toast$.display(result)
        })
    }
}