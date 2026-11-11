class StepsProgress extends Tag {
    init() {
        
    }

    set parameters(p) {
   //     if (p != this._parameters) {
            this._parameters = p
            this.getSteps()
     //   }
    }

    get parameters() {
        return this._parameters
    }

    clickedStep(step) {
        if (step.link) {
            let link = step.link.replaceAll('{contact_id}',this.parameters.contact_id)
            link = link.replaceAll('{user_id}',loggedInUser$.id)
            router$.goto(link)
        }
    }

    getSteps() {
        resource.action('get_steps',this.parameters).then(result=> {
            this.setAttribute('steps',result)
       //     this.fetchCurrentStep()
        })
    }

    refresh() {
        this.getSteps()
    }

/*
    fetchCurrentStep() {
        GlobalResource.action('steps-progress','current_step',{customer_id:this.customer.id}).then(result=>{
            resource.action('get_step_actions',{'customer_id':this.customer.id, 'contact_id':this.contact.id, 'current_step':result.currentStep}).then(result=>{
                this.setAttribute('stepActions',result)
            })
        })
    }*/
}