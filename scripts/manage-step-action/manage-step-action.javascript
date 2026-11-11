class ManageStepAction extends Tag {
    init() {
        this.stepActionID = router$.params.tab
        this.fetchStepAction()
        this.fetchStepActionTemplates()
    }

    fetchStepAction() {
        resource.action('get_step_action', {id:this.stepActionID}).then(result=> {
            this.setAttribute('stepAction', result)
            this.setAttribute('stepActionTemplate', result.step_action_template_obj)
        })
    }

    fetchStepActionTemplates() {
        resource.action('get_action_templates').then(result=> {
            this.setAttribute('stepActionTemplates', result)
        })
    }

    templateChanged(templateName) {
        let stepActionTemplate = this.stepActionTemplates.find( t => t.value == templateName)
        this.setAttribute('stepActionTemplate', stepActionTemplate)
        this.stepAction.step_action_template = stepActionTemplate.value
    }

    save() {
        if (this.params.isValid() && this.stepActionEl.isValid()) {
            this.stepAction.title = this.stepActionEl?.value?.title
            this.stepAction.parameters = this.params.value
            if (this.step_type_id) {
                this.stepAction.step_type_id = this.step_type_id
                this.stepAction.id = 'new'
            }
            resource.action('save',this.stepAction).then(result=> {
                toast$.display(result)
                if (result.new_step_action_id) {
                    router$.goto('/manage-step-action/'+result.new_step_action_id)
                }
            })
        }
    }
}