class DealLostTag extends Tag {
    init() {
        this.contact_id = router$.params.tab
    }

    markAsLost(data) {
        data.contact_id = this.contact_id
        resource.call(data).then(result=> {
            toast$.display(result)
            router$.goto('/customers')
        });
    }    
}