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
        resource.action('check_status', { invitation_code: this.invitation_code }).then(result => {
            this.setAttribute('confirmed', result.takes_part)
        })
    }

    getUrlParameter(name) {
        const urlParams = new URLSearchParams(window.location.search);
        return urlParams.get(name);
    }

    getEvent() {
        let timezone = Intl.DateTimeFormat().resolvedOptions().timeZone;
        resource.action('get_event', { event_name: this.event_name, timezone }).then(result => {
            let eventDateOptions = result.dates.map(v => ({
                value: v.id,
                name: v.start_date_formatted
            }));
            this.setAttribute('eventDateOptions',eventDateOptions)
            this.setAttribute('event', result)
        })
    }

    confirmParticipation() {
        let timezone = Intl.DateTimeFormat().resolvedOptions().timeZone;
        resource.action('confirm', { invitation_code: this.invitation_code, event_name: this.event_name, event_date:this.eventDateEl.value, timezone }).then(result => {
            toast$.display(result)
            this.setAttribute('confirmed', true)
        })
    }

    registerForEvent(data) {
        data.event_id = this.event.id
        data.partner_code = this.partner_code
        data.event_date = this.eventDateEl.value
        data.timezone = Intl.DateTimeFormat().resolvedOptions().timeZone;
        resource.action('register', data).then(result => {
            if (result.success) {
                this.setAttribute('confirmed', true)
            }
            toast$.display(result)
        })
    }


    render() {
        this.addEventInvitationClass()
    }

    addEventInvitationClass() {
        const appElement = document.querySelector('body .app');
        if (appElement) {
            appElement.classList.add('event-invitation-page');
        }
    }

}