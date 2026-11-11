class RequestSignature extends Tag {
    init() {
        if (router$.params.tab) {
            resource.action('get_contract_template_id', {name:router$.params.tab}).then(result=> {

                this.setAttribute('formValue', {contract_template_id:result.id, contact_id:router$.params.id})
            })
        }
    }

    requestSignature(data) {
        this.setAttribute('loading', true)
        resource.action('request_signature', data).then(result=> {
            toast$.display(result)
            router$.goto('/list-contracts')
            this.setAttribute('loading', false)
        })
    }
}