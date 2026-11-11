class ViewContract extends Tag {
    init() {
        if (window.router$) {
            this.contract_id = router$.params.tab
            this.loadContract()
        }
    }

    set contract_id(ci) {
        this._contract_id = ci
        this.loadContract()
    }

    get contract_id() {
        return this._contract_id
    }

    loadContract() {
        resource.action('get_contract', {contract_id:this.contract_id}).then(result=>{
            this.setAttribute('contract',result)
            if (window.toast$) {
                toast$.display(result)
            }
        })
    }

    toPDF() {
        resource.action('generate-pdf', {contract_id:this.contract_id}).then(result=>{
            
        })
    }

    signDocument(filename) {
        this.setAttribute('loading', true)
        resource.action('sign_document', {filename, contract_id:this.contract_id}).then(result=> {
            toast$.display(result)
            this.loadContract()
            this.setAttribute('loading', false)
        })
    }
}