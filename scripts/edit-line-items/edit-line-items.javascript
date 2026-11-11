class EditLineItemsTag extends Tag {
    setup() {
        this.document_id = router$.params.tab
    }

    updateCurrency() {
        resource.action('get_currency', {document_id:this.document_id}).then(result=> {
            Tag.ready('sales_document_item').then(loaded=> {
                let currencySymbol = result.currency_symbol
                Resource.getResource('sales_document_item').parameters.find( v => v.name == 'unit_price' ).options.suffix = currencySymbol
                this.databaseTableEditor.update('this.objs')
            })
        })
    }

    set document_id(di) {
        this._document_id = di
        if (di) {
            this.updateCurrency()
        }
        this.update('this.document_id')
    }

    get document_id() {
        return this._document_id
    }
}