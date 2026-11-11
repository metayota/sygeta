class DataEditor extends Tag {
    init() {
        let table = router$.params.table

        resource.action('get_title', {table}).then(result=> {
            document.title = result
            App.pushBreadcrumbs(true);
        })
    }
}