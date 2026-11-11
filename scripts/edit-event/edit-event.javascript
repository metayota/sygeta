class EditEvent extends Tag {
    setup() {
        this.resource = 'edit-event';
    }

    init() {
        this.setAttribute('loading', true);
        this.setAttribute('eventData', {});
        
        // Load event data if event_id is available in router params
        if (router$.params.tab) {
            this.loadEventData(router$.params.tab);
        } else {
            this.setAttribute('loading', false);
        }
    }

    loadEventData(eventId) {
        resource.action('load', { event_id: eventId }).then(result=> {
            toast$.display(result)
            this.setAttribute('eventData', result);
            this.setAttribute('loading', false);
        })
    }

    async handleSubmit(event) {
        this.setAttribute('loading', true);


        const formData = event;
        let participants = this.form.parameterEl.element_participants.value
        formData.participants = participants // add participants
        // Add event_id from router params
        formData.event_id = router$.params.event_id;

        resource.action('submit', formData).then(result=> {
            toast$.display(result)
            router$.goto('/calendar');
        })
    }
}