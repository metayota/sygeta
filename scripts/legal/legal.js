class LegalTag extends Tag {
    init() {
        resource.action('get_legal_text', {name:router$.params.tab}).then(result=> {
            this.setAttribute('legalText', result)
        })
    }
}