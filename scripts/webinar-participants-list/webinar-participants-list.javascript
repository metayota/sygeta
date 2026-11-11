class EventParticipantsList extends Tag {
    init() {
        this.event_id = router$.params.tab
        this.loadParticipants()
    }
    loadParticipants() {
        resource.action('get_participants', { event_id: this.event_id }).then(result => {
            this.setAttribute('participants', result)
        })
    }
}