class ViewDate extends Tag {

    set value(v) {
        this._value = v
        this.update('this.value')
        const jsDate = new Date(v);
        const options = { year: 'numeric', month: 'long', day: 'numeric' };
        let localeCode = translate('language_code_long')
        if (localeCode == undefined) {
            console.error('Metayota ViewDate Component - Please add the translation *language_code_long* like de-DE and make it accessible to JavaScript')
        } else {
            localeCode = 'en-US'
        }
        
        this.formatted = jsDate.toLocaleDateString(localeCode,options);
        this.update('this.formatted');
    }
    get value() {
        this._value
    }
}