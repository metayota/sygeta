class DBRowOptions extends RCBaseFormElement {

    set value(v) {
        this._value = v
        this.update('this.value')
    }

    get value() {
        return this._value
    }

    set name(n) {
        this._name = n
    }

    get name() {
        return this._name
    }

    changed(value) {
        this._value = value
        this.isValid()
        this.fire('change',value)
    }

    set dbtable(t) {
        this._dbtable = t
    }

    get dbtable() {
        return this._dbtable
    }

    set titlefield(t) {
        this._titlefield = t
    }

    get titlefield() {
        return this._titlefield
    }

    set idfield(i) {
        this._idfield = i
    }

    get idfield() {
        return this._idfield
    }

    set where(w) {
        this._where = w
    }

    get where() {
        return this._where
    }

    init() {
        this.fetchOptions()
    }

    set errorMessage(em) {
        this._errorMessage = em
        if (this.dropdownEl) {
            this.dropdownEl.errorMessage = em
        }
    }

    get errorMessage() {
        return this._errorMessage
    }

    fetchOptions() {
        if (!this.idfield || !this.titlefield || !this.dbtable) {
            return
        }
        // Tag.callJson('db.fetchall',{table:this.dbtable, fields:[this.idfield,this.titlefield],serverdb:this.serverdb})
        let translate_columns = this.translate_options ?  [this.titlefield] : []

        GlobalResource.call('db.fetchall', {table:this.dbtable, where: this.where,fields:[this.idfield,this.titlefield],serverdb:this.serverdb,translate_columns}, true).then( options => {
            this.options = options.map( v => {
                let translatedProperty = this.titlefield + '_translated'
                let displayValue = v[translatedProperty] != undefined ? v[translatedProperty] : v[this.titlefield]
                return {value: v[this.idfield], name: displayValue}
            })
            this.update('this.options')
        })
    }

 
}