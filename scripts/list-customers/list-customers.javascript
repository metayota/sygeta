class ListCustomers extends Tag {
  init() {
    resource.action('get_properties').then(result=> {
        this.setAttribute('properties',result)
    })
  }
}