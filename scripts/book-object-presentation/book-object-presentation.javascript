class BookObjectPresentation extends Tag {
    init() {
        this.fetchObjectPresentationPossible()
        this.fetchAvailableDates()
        this.fetchProperties()
        this.fetchBookedPresentations()
    }

    fetchProperties() {
        resource.action('get_properties').then(result=> {
            this.setAttribute('properties',result)
        })
    }

    fetchBookedPresentations() {
        resource.action('get_booked').then(result=>{
            this.setAttribute('bookedPresentations',result)
        })
    }

    fetchAvailableDates() {
        resource.action('get_available_dates').then(result => {
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

    fetchObjectPresentationPossible() {
        resource.action('check_availability').then(result => {
            this.setAttribute('possible', result.possible)
            this.setAttribute('possibleMessage', result.message)
        })
    }

    book() {
        let date = this.dateEl.value
        let time = this.timeEl.value
        let property_id = this.propertyEl.value
        resource.action('book',{date,time,property_id}).then(result=>{
            toast$.display(result)
            this.fetchBookedPresentations()
        })
    }
}