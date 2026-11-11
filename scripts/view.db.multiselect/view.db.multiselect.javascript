class DBMultiSelectView extends Tag {
    init() {
        this.getSelection()
    }
    getSelection() {
        if (this.reference_table) {
            let ownIdField = this.own_id_field ? this.own_id_field : 'id';
          //  let objID = (this.parentTag.object_id) ? this.parentTag.object_id : this.parentTag.parentTag._value[ownIdField]
            let ownID = this.parentTag.parentTag._value ? this.parentTag.parentTag._value[ownIdField] : undefined;
            if (ownID) {
                resource.action('get_db', { own_id: ownID, own_id_column: this.own_id_column, other_id_column: this.other_id_column, reference_table: this.reference_table, value: this.value }).then(result => {
                    this.setAttribute('value', result)
                })
            }
        }


    }
}