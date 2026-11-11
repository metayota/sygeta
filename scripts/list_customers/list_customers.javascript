class ListCustomers extends Tag {
  init() {
    this.properties = await resource.action('get_properties')
  }
}