class ChangePasswordTag extends Tag {
    changePassword(values) {
        resource.action('update_password',values).then(result => {
            toast$.display(result)
            this.passwordEl.reset()
        })
    }
}