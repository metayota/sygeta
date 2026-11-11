class CreateContact extends Tag {
    setup() {
        this.euCompliance = CreateContact.eu_compliance
    }

    resetForm() {
        this.form.value = { language: CreateContact.default_language }
        if (this.legalBasisForm) {
            this.legalBasisForm.value = {legal_basis_id:CreateContact.legitimate_interest_id}
        }
    }
    createContact() {
        debugger
        if (!this.legalBasisForm || this.legalBasisForm.isValid()) {
            let data = this.form.value
            if (this.legalBasisForm) {
                data.legal_basis = this.legalBasisForm.value
            }
            resource.action('create-contact', data).then(result => {
                toast$.display(result);
                this.resetForm()
            });
        }
    }
}
