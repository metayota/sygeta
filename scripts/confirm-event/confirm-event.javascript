class ConfirmEventTag extends Tag {
    getKeyFromHash() {
        const hash = window.location.hash; // e.g., "#key=abc123"
        const params = new URLSearchParams(hash.slice(1)); // remove the '#' and parse
        return params.get('key'); // returns 'abc123' or null if not present
    }
    init() {
        let page = router$.params.tab ? router$.params.tab : router$.params.page
        resource.action('get_info', { participant_id: page, access_key: this.getKeyFromHash() }).then(result => {

            this.setAttribute('contact', result.contact)
            this.setAttribute('event', result.event)
            this.setAttribute('participant', result.participant)
        })
    }
}