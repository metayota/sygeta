class FormDate extends RCBaseFormElement {
    setup() {
        this.type = 'date'
    }
    set value(v) {
        // Store the MySQL format internally
        this._value = v;
        // Convert to HTML datetime-local format for the input
        this._htmlValue = this.toHtmlFormat(v);
        this.update('this.value');
        this.update('this.htmlValue');
    }
    
    get value() {
        return this._value;
    }
    
    // Getter for the HTML input format
    get htmlValue() {
        return this._htmlValue;
    }

    set type(t) {
        if (t) {
            this._type = t
        }
    }

    get type() {
        return this._type
    }
    
    set label(l) {
        this._label = l;
        this.update('this.label');
    }
    
    get label() {
        return this._label;
    }
    
    // Convert MySQL datetime to HTML datetime-local format
    toHtmlFormat(mysqlDateTime) {
        if (!mysqlDateTime) return '';
        // Convert "2025-10-01 14:00:00" to "2025-10-01T14:00"
        return mysqlDateTime.slice(0, 16).replace(' ', 'T');
    }
    
    dateChanged(date) {
        // Convert to MySQL datetime format WITHOUT timezone conversion
        let mysqlDateTime;
        
        if (date.includes('T')) {
            // Firefox format: "2025-10-01T14:00" -> "2025-10-01 14:00:00"
            mysqlDateTime = date.replace('T', ' ');
            if (mysqlDateTime.length === 16) {
                mysqlDateTime += ':00'; // Add seconds if missing
            }
        } else {
            // Chrome format: already like "2025-10-01 14:00:00"
            mysqlDateTime = date;
            if (mysqlDateTime.length === 16) {
                mysqlDateTime += ':00'; // Add seconds if missing
            }
        }
        
        this._value = mysqlDateTime;
        this._htmlValue = this.toHtmlFormat(mysqlDateTime);
        this.fire('change', mysqlDateTime);
    }
}