class TimezoneHandler extends Tag {
    init() {
        let access_key = this.getKeyFromHash()
        this.accessKey = access_key
       // if (access_key) {
            let timezone = Intl.DateTimeFormat().resolvedOptions().timeZone;
            resource.action('update-timezone', {access_key, timezone}).then(result=> {
                if (result.ask_timezone_change) {
                    toast$.showCallback(undefined,result.message,function(){
                        this.fireChange()
                        window.timezone$ = timezone
                        notify('timezone$')
                        resource.action('update-timezone', {access_key, timezone, update_timezone:true}).then(result=> {
                            toast$.display(result)
                            window.setTimeout(function() {
                                document.location.reload()
                            }, 1000)
                        });
                    }.bind(this),result.update_btn)
                } else {
                    toast$.display(result)
                }
            })
    //    }
    }
    fireChange() {
        this.ignoreThis = true
        if (!this.ignoreThis) {
            this.fire('change',true)
        } 
        this.ignoreThis = false
    }
    getKeyFromHash() {
        const hash = window.location.hash; // e.g., "#key=abc123"
        const params = new URLSearchParams(hash.slice(1)); // remove the '#' and parse
        return params.get('key'); // returns 'abc123' or null if not present
    }
}