class ListWebinars extends Tag {
  init() {
    resource.action('list').then(result => {
        this.setAttribute('webinars',result);
    });
  }
}