class EventDetail extends Tag {
    init() {
        this.event_id = router$.params.tab
        resource.action('get', { id: router$.params.tab }).then(result => {
            this.setAttribute('event', result)
        })
        this.fetchPartnerLink()
    }
    fetchPartnerLink() {
        resource.action('get_partner_link',{event_id:this.event_id}).then(result=> {
            this.setAttribute('partnerLink',result.partner_link)
        })
    }
    copyPartnerLink(node) {
        node.select()
        document.execCommand("copy");
    }
}