class ManageStepType extends Tag {
    init() {
        this.setAttribute('stepTypeID',router$.params.tab)
        this.fetchStepTypeTemplates()
        this.fetchStepType()
    }

    templateChanged(stepTypeTemplateName) {
        let stepTypeTemplate = this.stepTypeTemplates.find( val => val.name == stepTypeTemplateName)
        this.stepType.step_type_template_params = {}
        this.stepType.step_type_template_obj = stepTypeTemplate
        this.stepType.step_type_template = stepTypeTemplate?.name
        this.update('this.stepType')
    }

    fetchStepTypeTemplates() {
        resource.action('get_step_type_templates').then(result=> {
            let mappedTemplates = result.map( t => {
                return {'name': t.title_translated, 'value': t.name}
            })
            this.setAttribute('stepTypeTemplateOptions', mappedTemplates)
            this.setAttribute('stepTypeTemplates', result)
        })
    }

    fetchStepType() {
        resource.action('get_step_type', {step_type_id:this.stepTypeID}).then(result=> {
            this.setAttribute('stepType',result)
        })
    }

    saveStepType() {
        if (this.params.isValid() && this.stepTypeForm.isValid()) {
            this.stepType.title = this.stepTypeForm.value.title
            this.stepType.name = this.stepTypeForm.value.name
            this.stepType.hidden = this.stepTypeForm.value.hidden
            this.stepType.order_no = this.stepTypeForm.value.order_no
            this.stepType.link = this.stepTypeForm.value.link
            this.stepType.step_type_template_params = this.params.value
            resource.action('save', this.stepType).then(result=> {
                toast$.display(result)
                if (result.new_step_type_id) {
                    router$.goto('/manage-step-type/'+result.new_step_type_id)
                }
            })
        }
    }
}