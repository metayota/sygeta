class DatabaseRowCreator extends Tag {
    init() {

    }

    set resource_name(rn) {
        if (rn && !this.title) {
            resource.action('get_texts',{table:rn}).then(result=>{
                this.setAttribute('title', result.title)
            })
        }
        this._resource_name = rn
        this.update('this.resource_name')
    }

    get resource_name() {
        return this._resource_name
    }

    save(data) {
        let saveData = { data, resource_name: this.resource_name }
        resource.action('save', saveData).then(result => {
            toast$.display(result)
            let parameterElement = this.form.parameterEl
            for (let parameter of parameterElement.parameters) {
                let foundEl = parameterElement['element_' + parameter.name]
                let tag = foundEl?.include?.renderedTag?.ownsTag
                if (tag && tag.processInsert) {
                    tag.processInsert(result.insert_id)
                }
            }
            if (this.return_path) {
                let returnPath = this.return_path.replace(/\[table\]/g, this.resource_name)
                returnPath = returnPath.replace(/\[id\]/g, result.insert_id)
                router$.goto(returnPath)
            } else {
                if (app$.breadcrumbs?.length > 0) {
                    router$.goto(app$.breadcrumbs[app$.breadcrumbs.length-1].url)
                }
            }
        })
    }
}