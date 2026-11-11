class SalesPipelineOverview extends Tag {
    init() {
        resource.call().then(result=> {
            this.setAttribute('steps', result)
        })
    }
}