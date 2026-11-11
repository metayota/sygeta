class NewsList extends Tag {
  init() {
    resource.action('get_news').then(result => {
      this.setAttribute('news', result);
    });
  }
}