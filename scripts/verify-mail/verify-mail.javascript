class ActivateMailTag extends Tag {
    resend(event) {
        resource.action('resend',{ mail: event.mail }).then(result => {
            toast$.show(result.message, true, result.type)
        })
    }
}