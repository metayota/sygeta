class SalesOverview extends Tag {
    init() {
        resource.action('get_statistics').then(result=> {
            this.setAttribute('invitation_count',result.invitation_count)
            this.setAttribute('customer_count',result.customer_count)
            this.setAttribute('account_balance',result.account_balance)
            this.setAttribute('total_income',result.total_income)
            this.setAttribute('sales_activity_count',result.sales_activity_count)
            this.setAttribute('mails_sent',result.mails_sent)
            this.setAttribute('contact_count',result.contact_count)
            this.setAttribute('meetings_count',result.meetings_count)
        })
    }
}