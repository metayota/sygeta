class MailManager extends Tag {
    init() {
        this.loadMails()
    }

    loadMails() {
        resource.action('get_mail_accounts', { server_id: this.server }).then(result => {
            this.setAttribute('mail_accounts', result)
        })
    }

    addMailAccount() {
        DialogForm.createDialogWithData('mail-account-data',{}).then(result=> {
            var parts = result.mail.split('@');

            // Zuweisen von Benutzername und Domain zu neuen Eigenschaften
            let username = parts[0];
            let domain = parts[1];
            let password = result.password

            resource.action('add_mail_account', { 'server_id': this.server, 'username': username, 'domain': domain, 'password': password }).then(result => {
                this.loadMails()
            })
        });
        
       
    }

    changePassword(mail_user_id) {
        let password = prompt('Enter a new password');
        resource.action('change_password', { 'server_id': this.server, 'password': password, 'mail_user_id': mail_user_id }).then(result => {
            this.loadMails()
        }) 
    }

    addAlias(mail_user_id) {
        DialogForm.createDialogWithData('mail-address',{}).then(result=> {
            resource.action('add_alias', { 'server_id': this.server, 'email': result.mail, 'mail_user_id': mail_user_id }).then(result => {
                this.loadMails()
            })
        })
    }

    deleteAlias(id) {
        resource.action('delete_alias', { server_id: this.server, alias_id: id }).then(result => {
            toast$.show('The mail alias was deleted successfully')
            this.loadMails()
        });
    }

    set server(server_id) {
        this._server = server_id
    }

    get server() {
        return this._server
    }
}