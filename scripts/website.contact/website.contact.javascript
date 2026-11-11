class WebsiteContact extends Tag {
    sendMail(data) {
        resource.call(data).then( ok => {
            this.resForm.reset()
            this.setAttribute('submitted', true)
        })
    }
}