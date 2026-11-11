class MailSettings extends Tag {
    init() {
        this.getMailSettings()
    }

    getKeyFromHash() {
        const hash = window.location.hash; // e.g., "#key=abc123"
        const params = new URLSearchParams(hash.slice(1)); // remove the '#' and parse
        return params.get('access_key'); // returns 'abc123' or null if not present
    }

    getMailSettings() {
        resource.call({access_key:this.getKeyFromHash()}).then(result=> {
            this.setAttribute('mail_settings',result)
        })
    }

    changedSetting(type_name,value) {
        resource.action('change_setting',{type_name,value, access_key:this.getKeyFromHash()}).then(result=>{
            toast$.display(result)
        })
    }
}