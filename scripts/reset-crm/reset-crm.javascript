class ResetCRM extends Tag {
    resetCRM() {
        resource.action('reset',this.resetCrmForm.value).then(result=> {
            toast$.display(result)
        })
    }
}