class EventInvitation extends Tag {
    set event_name(n) {
        this._event_name = n
        this.getEvent()
    }

    get event_name() {
        return this._event_name
    }

    set partner_code(c) {
        this._partner_code = c
    }

    get partner_code() {
        return this._partner_code
    }

    set invitation_code(c) {
        this._invitation_code = c
        this.checkInvitationStatus()
    }

    get invitation_code() {
        return this._invitation_code
    }

    checkInvitationStatus() {
        resource.action('check_status',{invitation_code:this.invitation_code}).then(result=> {
            this.setAttribute('confirmed',result.takes_part)
        })
    }

    getEvent() {
        resource.action('get_event',{event_name:this.event_name}).then(result=>{
            this.setAttribute('event',result)
        })
    }

    confirmParticipation() {
        resource.action('confirm',{invitation_code:this.invitation_code,event_name:this.event_name}).then(result=>{
            toast$.display(result)
            this.setAttribute('confirmed',true)
        })
    }

    registerForEvent(data) {
        data.event_id = this.event.id
        data.partner_code = this.partner_code
        resource.action('register',data).then(result=> {
            toast$.display(result)
            this.setAttribute('confirmed',true)
        })
    }
}