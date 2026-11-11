class WebinarDetail extends Tag {
  init() {
    resource.action('get', {id: router$.params.tab}).then(result=> {
        this.setAttribute('webinar',result)
    })
    
  }
  
  invite() {
    // Implement invite logic
  }
  
  register() {
    // Implement registration logic  
  }
}