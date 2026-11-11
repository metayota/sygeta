class AddDocumentTag extends Tag {
    init() {
        let contactID = isNaN(router$.params.tab) ? '' : router$.params.tab;
        this.setAttribute('contact_id',contactID)
        this.fetchDocumentTypes()
    }

    fetchDocumentTypes() {
        resource.action('get_document_types',{contact_id:this.contact_id}).then(result=>{
            const mappedArray = result.map(item => ({
                name: item.title_translated,
                value: item.id
            }));
            this.setAttribute('documentTypes',mappedArray)
            let selectedItem = document.location.hash?.substr(1)
            this.setAttribute('selectDocumentType', selectedItem)
        })
    }
    
    addDocument() {
        let data = {file:this.fileEl.value,document_type_id:this.documentTypeEl.value,contact_id:this.contact_id}
        resource.action('add_document',data).then(result=>{
            toast$.display(result)
            if (this.contact_id) {
                router$.goto('/manage-documents/'+this.contact_id)
            } else {

                router$.goto('/documents')
            }
        })
    }
}