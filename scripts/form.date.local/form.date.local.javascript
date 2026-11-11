class LocalFormDate extends RCBaseFormElement {
    setup() {
        this.type = 'datetime-local'
        this.userTimezone = 'UTC';
    }
    
    init() {
        this.loadTimezone().then(result => {
            this.userTimezone = result.timezone;
            this.setAttribute('userTimezone', result.timezone);
            // Re-convert current value with new timezone
            if (this._value) {
                this._htmlValue = this.toHtmlFormat(this._value);
                this.update('this.htmlValue');
            }
        })
    }
    
    loadTimezone() {
        return resource.action('get_timezone').then(result => {
            return result;
        })
    }

    // Value is always UTC
    set value(v) {
        this._value = v; // Store UTC
        this._htmlValue = this.toHtmlFormat(v); // Convert UTC to user timezone
        this.update('this.value');
    }
    
    get value() {
        return this._value;
    }

    set htmlValue(v) {
        this._htmlValue = v
        this.update('this.htmlValue')
    }
    
    get htmlValue() {
        return this._htmlValue;
    }
    
    set label(l) {
        this._label = l;
        this.update('this.label');
    }
    
    get label() {
        return this._label;
    }
    
    // Convert UTC MySQL datetime to user timezone, then put T in
    toHtmlFormat(utcDateTime) {
        if (!utcDateTime || !this.userTimezone) return '';
        
        // Convert UTC to user timezone
        const utcDate = new Date(utcDateTime + 'Z');
        const userLocalTime = new Date(utcDate.toLocaleString("en-US", {timeZone: this.userTimezone}));
        
        const year = userLocalTime.getFullYear();
        const month = String(userLocalTime.getMonth() + 1).padStart(2, '0');
        const day = String(userLocalTime.getDate()).padStart(2, '0');
        const hours = String(userLocalTime.getHours()).padStart(2, '0');
        const minutes = String(userLocalTime.getMinutes()).padStart(2, '0');
        
        return `${year}-${month}-${day}T${hours}:${minutes}`;
    }
    
    dateChanged(date) {
        if (!date || !this.userTimezone) return;
        
        // Convert user input back to UTC
        const userDate = new Date(date); // User's timezone input
        
        // Convert to UTC
        const utcTime = new Date(userDate.toLocaleString("en-US", {timeZone: "UTC"}));
        const userTime = new Date(userDate.toLocaleString("en-US", {timeZone: this.userTimezone}));
        const offset = userDate.getTime() - userTime.getTime();
        const utcDate = new Date(userDate.getTime() + offset);
        
        // Convert to MySQL format
        let mysqlDateTime = utcDate.toISOString().slice(0, 19).replace('T', ' ');
        
        this._value = mysqlDateTime; // Store as UTC
        this._htmlValue = date; // Keep user input
        this.fire('change', mysqlDateTime); // Fire UTC
    }
    
    timezoneChanged(event) {
        this.userTimezone = event.detail.timezone;
        if (this._value) {
            this._htmlValue = this.toHtmlFormat(this._value);
            this.update('this.htmlValue');
        }
    }
}