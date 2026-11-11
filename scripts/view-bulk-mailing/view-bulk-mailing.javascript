class ViewBulkMailing extends Tag {
    init() {
        this.load()
    }

    load() {
        resource.action('get_mailing',{id:router$.params.tab}).then(result=>{
            this.setAttribute('mailing',result)
        })
    }

    regenerate() {
        resource.action('regenerate',this.mailing).then(result=> {
            toast$.display(result)
            this.load()
        })
    }

    generateZIP() {
        resource.action('generate_zip',this.mailing).then(result=> {
            
        });
    }

    markAsSent(receiver, sent) {
        resource.action('mark_as_sent',{receiver,sent}).then(result=> {
            toast$.display(result)
            this.load()
        })
    }
}