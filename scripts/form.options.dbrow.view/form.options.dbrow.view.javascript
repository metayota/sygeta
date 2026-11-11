class FormOptionsDBRowView extends Tag {
    set value(v) {
        if (this._value != v) {
            this._value = v
            this.updateValueTitle()
        }
    }

    get value() {
        return this._value
    }

    getDisplayValue() {
        if (this.value_resolved) {
            return this.value_resolved
        }
        return this.translate_options && this.valueTitleTranslated ? this.valueTitleTranslated : this.valueTitle
    }

    updateValueTitle() {
        if (this._value) {
            if (!this.value_resolved) {
                let where = {[this.idfield] : this._value};
                let translate_columns = this.translate_options ?  [this.titlefield] : []
                let table = this.dbtable
                Tag.callJson('db.get',{where,table,translate_columns}).then( row => {
                    this.valueTitle = row[this.titlefield]
                    this.valueTitleTranslated = row[this.titlefield+"_translated"]
                    this.update('this.getDisplayValue')
                })
            }
        } else {
            this.valueTitle = ''
            this.update('this.getDisplayValue')
        }
    }
}