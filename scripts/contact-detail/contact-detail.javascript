class ContactDetail extends Tag {
    init() {
        this.contactID = router$.params.tab ? router$.params.tab : router$.params.page
        this.loadContact()
    }

    loadContact() {
        resource.action('get_contact',{id:this.contactID}).then(result=> {
            this.setAttribute('contact',result)
        })
    }
}