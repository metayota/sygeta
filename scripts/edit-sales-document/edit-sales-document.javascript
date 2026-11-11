class EditSalesDocument extends Tag {
    init() {
        this.document_id = router$.params.tab ? router$.params.tab : router$.params.subpage
        this.document = {};
        this.loadDocument();
    }

    loadDocument() {
        resource.action('load', {
            id: this.document_id
        }).then(result => {
            this.setAttribute('document', result.document);
            this.setAttribute('documentType', result.documentType)
        });
    }

    getHiddenParameters(documentType) {
        let hiddenParameters = []
        if (!documentType) {
            return hiddenParameters
        }
        if (!documentType.has_line_items) {
            hiddenParameters.push('currency')
            hiddenParameters.push('payment_terms')
            hiddenParameters.push('delivery_date')
        }
        if (!documentType.has_body_text) {
            hiddenParameters.push('body_text')
        }
        if (!documentType.due_date_days) {
            hiddenParameters.push('due_date')
        }
        return hiddenParameters
    } 

    saveCustomForm(customFormData) {
        resource.action('save_custom', {id:this.document_id, custom_form_data:customFormData}).then(result=> {
            toast$.display(result)
        })
    }

    save(formData) {
        if (this.document?.currency != formData.currency && this.lineItems) {
            this.lineItems.updateCurrency()
        }
        resource.action('save', {
            id: this.document_id,
            document: formData
        }).then((result) => {
            this.loadDocument();
            toast$.display(result)
        });
    }
}