class ManageAPIKeys extends Tag {
    init() {
        this.fetchExistingKeys()
    }

    userSelected(user_id) {
        resource.action('get-usergroup', {user_id}).then(result=> {
            this.setAttribute('selectedUserUsergroup', result.usergroupTitle)
        })
    }

    switchUserSelect(selectedUserOption) {
        this.setAttribute('selectedUserOption', selectedUserOption)
        this.setAttribute('selectedUserUsergroup', undefined );
    }

    generateKey(data) {
        if (this.selectedUserOption == 'existing_user' && this.userSelectEl) {
            data.user_id = this.userSelectEl.value
        }
        resource.action('generate-key', data).then(result=>{
            toast$.display(result)
            if (this.createUserForm) {
                this.createUserForm.value = {}
            }
            this.fetchExistingKeys()
        })
    }

    fetchExistingKeys() {
        resource.action('get-existing-keys').then(result=>{
            this.setAttribute('existingKeys', result)
        })
    }

    deleteAPIKey(data) {
        resource.action('delete-api-key', data).then(result => {
            toast$.display(result)
            this.fetchExistingKeys()
        })
    }

}