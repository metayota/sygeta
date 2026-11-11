class CalendarAdd extends Tag {
    submitEvent(data) {
        let participants = this.form.parameterEl.getElements().find(el => el.name == 'participants').value
        data.participants = participants // add participants
        resource.action('submit_event', data).then(result => {
            toast$.display(result)
            if (result.success) {
                router$.goto('/calendar')
            }
        })
    }
}