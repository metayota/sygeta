class LineDiagramEditor extends Tag {
    setup() {
        this.line_diagram_id = router$.params.tab ? router$.params.tab : router$.params.subpage
        this.loadSeries()
    }

    loadSeries() {
        resource.action('get_series',{diagram_id:this.line_diagram_id}).then(result=> {
            this.setAttribute('series',result)
        })
    }

    refresh() {
        this.lineDiagram.refreshData()
    }
}