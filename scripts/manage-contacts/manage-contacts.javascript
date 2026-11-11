class ManageContacts extends Tag {
    setup() {
        this.fetchStepTypes()
        this.where = {user_id:loggedInUser$.id}
        this.show_all = false
        resource.action('get_partner_id').then(result=> {
            if (result && result.partner_id) {
                this.where.partner_id = result.partner_id
                this.setAttribute('where',this.where)
            }
        })
    }

    loadContacts(stepType) {
        this.setAttribute('where', {step_type_id:stepType})
    }

    showAll() {
        this.setAttribute('where',{})
        this.setAttribute('show_all',true)
    }

    showMyContacts() {
        this.setAttribute('where',{user_id:loggedInUser$.id})
        this.setAttribute('show_all',false)
    }

    fetchStepTypes() {
        resource.action('get_step_types').then(result=> {
            this.setAttribute('stepTypes', result)
        })
    }
}