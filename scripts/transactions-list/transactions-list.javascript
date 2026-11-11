class TransactionsList extends Tag {
  init() {
    resource.action('list').then(result => {
      this.setAttribute('transactions', result);
    });
  }
}