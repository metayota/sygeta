class ForgotPasswordTag extends Tag {
    forgotPassword(data) {
        resource.call(data).then(result=> {
            toast$.show(result.message,true,result.type)
        })
        this.forgot.clear()
    }
}