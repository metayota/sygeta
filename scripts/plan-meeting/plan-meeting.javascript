class PlanMeetingTag extends Tag {
    init() {
        this.fetchData()
    }

    fetchData() {
        resource.action('get_data', {meeting_type:this.meeting_type, contact_id:this.contact_id}).then(result=>{
            this.setAttribute('meetingType', result.meetingType)
            this.setAttribute('contact', result.contact)
            this.setAttribute('formTitle', result.formTitle)
            this.setAttribute('formDescription', result.formDescription)
        })
    }

    sendInvitation(data) {
        data.contact_id = this.contact.id
        data.meeting_type = this.meetingType.name
        data.form_data_included = true
        GlobalResource.action('book-meeting', 'invite', data).then(result=> {
            toast$.display(result)
            router$.goto('/contact-detail/'+this.contact.id)
        })
    }
}