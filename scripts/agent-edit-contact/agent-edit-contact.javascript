class OwnContact extends Tag {
    init() {
        resource.action('get_contact').then(result=>{
            this.setAttribute('ownContact',result)
        })
    }

    save(data) {
        resource.action('save',data).then(result=>{
            toast$.display(result)
        })
    }
}