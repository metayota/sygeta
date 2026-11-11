class CreateSalesDocumentTag extends Tag {
    init() {
        this.parseHashParams();
    }

    parseHashParams() {
        const hash = window.location.hash.substring(1); // Remove the #
        
        if (hash) {
            const params = {};
            hash.split('&').forEach(param => {
                const [key, value] = param.split('=');
                if (key && value) {
                    params[key] = decodeURIComponent(value);
                }
            });
            
            // Only set if we have parameters
            if (Object.keys(params).length > 0) {
                this.setAttribute('value', params);
                this.itemChanged(this.value)
            }
        }
    }

    async createSalesDocument() {
        let data = this.salesDocumentEl.value

        if (this.customFormEl) {
            data.custom_form_data = JSON.stringify(this.customFormEl.value) 
        }

        if (this.document_type.has_body_text) {

            data.body_text = this.bodyTextEl?.value
            data.title = this.titleEl.value
            let autosaveTitle = this.titleEl.checkAutoTranslate(false, true, false)
            let autosaveText = this.bodyTextEl.checkAutoTranslate(false, true, false)
            let finished = Promise.all([autosaveTitle,autosaveText])
            this.setAttribute('waiting', true);
            await finished;
        }


        resource.action('create', data).then(result=>{
            toast$.display(result)
            if (result.success) {
                if (this.document_type.has_line_items) {
                    router$.goto('/edit-line-items/'+result.id)
                } else {
                    router$.goto('/sales-document-detail-view/'+result.id)
                }
            }
        })
    }

    itemChanged(data) {
        if (data.document_type != this.document_type?.name) {
            resource.action('get_document_type', {document_type:data.document_type}).then(result=>{
                this.setAttribute('document_type', result)
            })
        }
    }
}