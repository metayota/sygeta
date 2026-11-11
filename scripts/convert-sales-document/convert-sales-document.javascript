class ConvertSalesDocument extends Tag {
    init() {
        this.document_id = router$.params.tab
    }

    convert(data) {
        data.document_id = this.document_id
        resource.action('convert',data).then(result=>{
            toast$.display(result)
            router$.goto('/sales-document-detail-view/'+result.id)
        })
    }
}