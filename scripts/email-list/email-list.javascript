class EMailList extends Tag {
    init() {
        
    }
    set contact_id(id) {
        if (id != this._contact_id) {
            this._contact_id = id
            resource.action('get_mails_to_customer',{contact_id:id}).then(result=> {
                this.setAttribute('mails',result)
            })
        }      
    }
    get contact_id() {
        return this._contact_id
    }

    formatDate(dateStr) {
        if (dateStr == undefined) {
            return ''
        }
        let date = new Date(dateStr);
        let now = new Date();
        let options = { year: 'numeric', month: 'short', day: 'numeric', hour: 'numeric', minute: 'numeric' };//, second: 'numeric' 
        let formatter = new Intl.DateTimeFormat(translate('language_code_long'), options);
        if (now.getFullYear() == date.getFullYear()) {
            delete options.year;
            if (now.getMonth() == date.getMonth() && now.getDay() == date.getDay()) {
                delete options.year;
                delete options.day;
                delete options.month;
            }
        }

        return (formatter.format(date));
    }
}