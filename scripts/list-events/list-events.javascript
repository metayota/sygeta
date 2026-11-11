class ListEvents extends Tag {
  init() {
    resource.action('list').then(result => {
        this.setAttribute('events',result);
    });
  }
}