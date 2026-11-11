class NewsDetail extends Tag {
  init() {
    let id = router$.params.tab;
    resource.action('get_news_detail', {id: id}).then(result => {
      this.setAttribute('news', result);
    });
  }
}