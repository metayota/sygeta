class NewsletterSender extends Tag {
    init() {
        this.newsletterID = router$.params.tab ? router$.params.tab : router$.params.page
        this.fetchNewsletter()
    }

    fetchNewsletter() {
        resource.action('get_newsletter',{id:this.newsletterID}).then(result=> {
            this.setAttribute('newsletter',result)
        })
    }

    send() {
        resource.action('queue',{id:this.newsletterID}).then(result => {
            toast$.display(result)
            this.setAttribute('newsletterSent',result.success)
        })
    }
}