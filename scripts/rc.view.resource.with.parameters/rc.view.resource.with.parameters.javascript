class ViewResWithParams extends Tag {
    setup() {
        this._value = ''
    }

    set value(v) {
        if (v && (typeof v == 'string')) {
            v = JSON.parse(v)
        }
        this.resourcetype = v?.resourcetype
        this.attributes = v?.options
        this.update('this.resourcetype')
        this.update('this.attributes')
    }

    get value() {
        return this._value
    }

    commaSeparated(obj) {
        if (!obj) {
            return ''
        }
         return Object.entries(obj)
            .filter(([key, value]) => Boolean(value)) // Falsy Werte ausschließen, aber 0 beibehalten
            .map(([key, value]) => `${key}: ${value}`)
            .join(', ');
    }
}