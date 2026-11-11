class AdminEventParticipants extends Tag {
    init() {
        this.fetchParticipants()
    }

    fetchParticipants() {
        resource.action('get_participants').then(result=> {
            this.setAttribute('participants',result)
        })
    }

    markAsAttended(participant) {
        resource.action('mark_as_attended',participant).then(result=> {
            toast$.display(result)
            this.fetchParticipants()
            GlobalResource.action('create-account','create',participant).then(result=> {
                toast$.display(result)
            })
        })
    }
}