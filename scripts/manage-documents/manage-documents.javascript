class ManageDocuments extends Tag {
    init() {
        let customerID = isNaN(router$.params.tab) ? '' : router$.params.tab;
        this.setAttribute('customer_id',customerID)
        this.fetchMissingDocuments(customerID)
        this.fetchComplete()
    }

    fetchMissingDocuments(customer_id) {
        resource.call({customer_id}).then(result=> {
            this.setAttribute('categories',result)
        })
    }
    fetchComplete() {
        resource.action('get_is_complete',{customer_id:this.customer_id}).then(result=> {
            this.setAttribute('complete',result.complete)
        })
    }
    confirmComplete() {
        if (this.completeCheckboxEl.value) {
            resource.action('confirm_complete',{customer_id:this.customer_id}).then(result=>{
                toast$.display(result)
                this.fetchComplete()
            })
        }
    }
}