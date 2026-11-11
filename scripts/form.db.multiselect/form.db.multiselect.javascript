class DBMultiSelect extends RCBaseFormElement {
    init() {
        this.getOptions()
        this.getSelection()
    }

    getOptions() {
        let translate_columns = this.translate_options ? [this.titlefield] : []

        GlobalResource.call('db.fetchall', { table: this.dbtable, where: this.where, fields: [this.idfield, this.titlefield], serverdb: this.serverdb, translate_columns }, true).then(options => {
            this.options = options.map(v => {
                let translatedProperty = this.titlefield + '_translated'
                let displayValue = v[translatedProperty] != undefined ? v[translatedProperty] : v[this.titlefield]
                return { value: v[this.idfield], name: displayValue }
            })
            this.update('this.options')
        })
    }

    selectionChanged(event) {
        this.value = event
        this.fire('change', event)
        this.changed = true
    }

    getSaveValue() {
        return false
    }

    set value(v) {
        this._value = v
    }

    get value() {
        return this._value
    }

    set own_id_column(oid) {
        if (this._own_id_column != oid) {
            this._own_id_column = oid;
        }
    }

    get own_id_column() {
        return this._own_id_column;
    }

    set other_id_column(oid) {
        if (this._other_id_column != oid) {
            this._other_id_column = oid;
        }
    }

    get other_id_column() {
        return this._other_id_column;
    }

    set reference_table(rt) {
        if (this._reference_table != rt) {
            this._reference_table = rt;
        }
    }

    get reference_table() {
        return this._reference_table;
    }

    set idfield(idf) {
        if (this._idfield != idf) {
            this._idfield = idf;
        }
    }

    get idfield() {
        return this._idfield;
    }

    set titlefield(tf) {
        if (this._titlefield != tf) {
            this._titlefield = tf;
        }
    }

    get titlefield() {
        return this._titlefield;
    }

    getSelection() {
        let ownIdField = this.own_id_field ? this.own_id_field : 'id';
        if (this.parentTag?.parentTag?._value) {
            let ownID = this.parentTag.parentTag._value[ownIdField]
        //    if (this.parentTag.parentTag._value && this.reference_table && ownID) {
                resource.action('get_db', { own_id: this.parentTag.parentTag._value[ownIdField], own_id_column: this.own_id_column, other_id_column: this.other_id_column, reference_table: this.reference_table, value: this.value }).then(result => {
                    this.setAttribute('value', result)
                })
       //     }
        }
    }

    processInsert(ownID) {
        resource.action('update_db', { own_table: this.own_table, table: this.dbtable, own_id: ownID, own_id_column: this.own_id_column, other_id_column: this.other_id_column, reference_table: this.reference_table, value: this.value }).then(result => {
            
        })
    }

    save() {
        let ownIdField = this.own_id_field ? this.own_id_field : 'id';

        if (this.changed && this.parentTag.parentTag._value[ownIdField]) {
            resource.action('update_db', { own_table: this.own_table, table: this.dbtable, own_id: this.parentTag.parentTag._value[ownIdField], own_id_column: this.own_id_column, other_id_column: this.other_id_column, reference_table: this.reference_table, value: this.value }).then(result => {
                this.changed = false
            })
        }

    }

    set errorMessage(e) {
        if (this.dropdownEl) {
            this.dropdownEl.setAttribute('errorMessage', e)
        }
    }
}