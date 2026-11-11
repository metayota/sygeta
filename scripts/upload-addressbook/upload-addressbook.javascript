class UploadAddressbookTag extends Tag {
    uploadAddressbook(data) {
        resource.call(data).then(result => {
            toast$.show(result.message,true,result.type)
            this.fire('change',null);
        })
    }
}