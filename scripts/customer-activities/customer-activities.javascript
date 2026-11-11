class CustomerActivities extends Tag {
    setup() {
        this.mode = 'standard'
    }

    getCurrentMySQLDate() {
        const now = new Date();
        return now.toISOString().slice(0, 10);
    }

    set contact_id(ci) {
        if (this._contact_id != ci) {
            this._contact_id = ci
            this.fetchActivities()
        }
    }

    get contact_id() {
        return this._contact_id
    }

    fetchActivities() {
        resource.action('get_activities',{contact_id:this.contact_id}).then(result => {
            this.setAttribute('contact_activities', result);
        });
    }
    
    submit(data) {
        data.contact_id = this.contact_id
        resource.action('submit_activity', data).then(result => {
            toast$.display(result)
            this.fetchActivities()
            this.setAttribute('mode','standard')
        });
    }
}