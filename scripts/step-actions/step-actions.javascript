class StepAction extends Tag {

    set contact_id(cid) {
        this._contact_id = cid
        if (cid) {
            this.fetchCurrentStep()
        }
    }

    get contact_id() {
        return this._contact_id
    }

    fetchCurrentStep() {
        GlobalResource.action('steps-progress', 'current_step', { contact_id: this.contact_id }).then(result => {
            resource.action('get_step_actions', { 'contact_id': this.contact_id,  'current_step': result.currentStep }).then(result => {
                this.setAttribute('stepActions', result)
            })
        })
    }

    performStep(stepAction) {
        if (stepAction.link) {
            router$.goto(stepAction.link)
        } else {
            this.setAttribute('performingAction', true)
            let parsedParameters = JSON.parse(stepAction.parameters);
            let params = { ...parsedParameters, contact_id: this.contact_id };
            GlobalResource.action(stepAction.resource, stepAction.action, params).then(result => {
                toast$.display(result)
                this.fire('refresh')
                this.setAttribute('performingAction', false)
                this.fetchCurrentStep()
                GlobalResource.action('webservices-steps', 'update-current-steps',{contact_id:this.contact_id})
                if (result.redirect) {
                    router$.goto(result.redirect)
                }
            })
        }
    }
}