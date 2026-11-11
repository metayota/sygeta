class CustomerDetail extends Tag {
    setup() {
        this.tab = 'sales_pipeline'
    }
/*
    fetchCurrentStep() {
        GlobalResource.action('steps-progress','current_step',{customer_id:this.customer.id}).then(result=>{
            resource.action('get_step_actions',{'customer_id':this.customer.id, 'contact_id':this.contact.id, 'current_step':result.currentStep}).then(result=>{
                this.setAttribute('stepActions',result)
            })
        })
    }

    performStep(stepAction) {
        if (stepAction.link) {
            router$.goto(stepAction.link.replaceAll('{customer_id}',this.customer.id))
        } else {
            this.setAttribute('performingAction',true)
            let parsedParameters = JSON.parse(stepAction.parameters);
            let params = {...parsedParameters, customer_id: this.customer.id,contact_id: this.contact.id};
            GlobalResource.action(stepAction.resource,stepAction.action,params).then(result=>{
                toast$.display(result)
                this.refresh()
                this.setAttribute('performingAction',false)
            })
        }
    }
    */
    init() {
        this.refresh()
    }
    refresh() {
        resource.call({id:router$.params.tab}).then(result=> {
            this.setAttribute('customer',result.customer)
            this.setAttribute('contact',result.contact)
            this.setAttribute('partner',result.partner)
            this.setAttribute('partner_contact',result.partner_contact)
            //this.fetchCurrentStep()
        })
    }
    saveCustomer(data) {
        resource.action('save_customer',data).then(result=> {
            toast$.display(result)
        })
    }
}