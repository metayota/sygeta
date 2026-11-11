class InviteParnter extends Tag {
    invite(data) {
        resource.call(data).then(result=> {
            toast$.display(result)
            this.form.reset();
        })
    }
}