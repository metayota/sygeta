class NewsList extends Tag {
  init() {
    this.loadNews()
  }
  
  loadNews() {
    resource.action('load_news').then(result => {
      this.setAttribute('news', result)
    })
  }
}