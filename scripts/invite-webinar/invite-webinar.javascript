class InviteWebinar extends Tag {
    setup() {
        this.webinar_id = router$.params.tab
    }
  init() {
    this.contacts = []
    this.selectedContacts = []
    this.invitationSent = false
    this.loadContacts()
    resource.action('get_webinar',{webinar_id:this.webinar_id}).then(result=> {
        this.setAttribute('webinar',result)
    });
  }

  loadContacts() {
    resource.action('get_uninvited_contacts', { webinar_id: this.webinar_id }).then(result => {
      this.setAttribute('contacts', result)
    })
  }

  toggleContact(contactId,invite) {
    const index = this.selectedContacts.indexOf(contactId)
    if (index > -1) {
        if (!invite) {
        this.selectedContacts.splice(index, 1)
        }
    } else {
        if (invite) {
            this.selectedContacts.push(contactId)
        }
    }
  }

  sendInvitations() {
    if (this.selectedContacts.length === 0) {
      alert(translate('select_at_least_one_contact'))
      return
    }
    resource.action('send_invitations', { 
      webinar_id: this.webinar_id,
      contact_ids: this.selectedContacts
    }).then(result => {
        toast$.display(result)
      if (result.success) {
        this.setAttribute('invitationSent', true)
        this.loadContacts()
        toast$.display(result)
      }
    })
  }
}