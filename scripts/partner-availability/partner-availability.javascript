class PartnerAvailability extends Tag {
    init() {
        resource.action('get_partner_id').then(result=>{
            this.setAttribute('partner_id',result.partner_id)
        })
    }
}