class SalesActivities extends Tag {
    init() {
        this.loadSalesActivities()
    }

    loadSalesActivities() {
        resource.action('get_sales_activities').then(result=> {
            this.setAttribute('salesActivities',result)
        })
    }
}