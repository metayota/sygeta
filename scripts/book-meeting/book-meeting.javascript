class BookObjectPresentation extends Tag {
    init() {
        this.accessKey = this.getKeyFromHash()
       /* this.meetingType = router$.params.tab
        if (this.meetingType) {
            this.fetchMeetingType()
        }*/
        this.fetchData()
    }

    fetchData() {
        this.fetchAvailableDates()
        this.fetchBookedMeetings()
        this.fetchMeetingInvitations()
    }

    getKeyFromHash() {
        const hash = window.location.hash; // e.g., "#key=abc123"
        const params = new URLSearchParams(hash.slice(1)); // remove the '#' and parse
        return params.get('key'); // returns 'abc123' or null if not present
    }

    set meeting_invitation_id(mi) {
        this._meeting_invitation_id = mi
    }

    get meeting_invitation_id() {
        return this._meeting_invitation_id
    }

    set meeting_type(mt) {
        this.meetingType = mt
        this.fetchMeetingType()
    }

    fetchMeetingInvitations() {
        if (this.accessKey && this.meeting_invitation_id) {
            resource.action('get_invitation', {access_key:this.accessKey, meeting_invitation_id:this.meeting_invitation_id}).then(result=> {
                this.setAttribute('meetingInvitation', result)
            })
        }
    }

    fetchMeetingType() {
        resource.action('get_meeting_type',{meeting_type:this.meetingType}).then(result=> {
            this.setAttribute('meetingTypeObj',result)
        })
    }


    fetchBookedMeetings() {
        resource.action('get_booked',{access_key:this.accessKey, meeting_type: this.meetingType}).then(result=>{
            this.setAttribute('bookedMeetings',result)
        })
    }

    fetchAvailableDates() {
        let meeting_invitation_id = this.meeting_invitation_id
        resource.action('get_available_dates',{access_key:this.accessKey, meeting_type: this.meetingType, meeting_invitation_id}).then(result => {
            this.setAttribute('dates', result)
        })
    }

    findTimesByDate(targetDate) {
        const foundDay = this.dates.find(day => day.value === targetDate);
        return foundDay ? foundDay.times : null;
    }


    pickedDate(data) {
        this.setAttribute('timeValue', undefined)
        this.setAttribute('times', this.findTimesByDate(data))
    }


    book() {
        let date = this.dateEl.value
        let time = this.timeEl.value
        let meeting_invitation_id = this.meeting_invitation_id
        resource.action('book',{date,time,meeting_invitation_id,meeting_type_id:this.meetingTypeObj.id,access_key:this.accessKey}).then(result=>{
            toast$.display(result)
            this.fetchBookedMeetings()
            this.fetchMeetingInvitations()
            if (result.success) {
                this.fire('booked', result.success)
                this.setAttribute('justBooked', true)
            }
        })
    }
}