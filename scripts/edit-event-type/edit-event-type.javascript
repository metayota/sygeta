class EditEventType extends Tag {
    init() {
        resource.action('get_event_type', {id:router$.params.tab}).then(result=> {
            this.setAttribute('eventType', result)
        })
    }
}