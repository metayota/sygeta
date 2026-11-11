class ContactLegalBasis extends Tag {
    set contact_id(id) {
        debugger
        if (this._contact_id != id) {
            this._contact_id = id
            this.fetchLegalBasis()
        }
    }
    get contact_id() {
        return this._contact_id
    }
    fetchLegalBasis() {
        resource.action('get_legal_basis', {contact_id:this.contact_id}).then(result=>{
            this.setAttribute('legalBasisList', result)
        })
    }
}