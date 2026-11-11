class InviteEvent extends Tag {
    setup() {
        this.event_id = router$.params.tab
    }
  init() {
    this.contacts = []
    this.selectedContacts = []
    this.invitationSent = false
    this.loadContacts()
    resource.action('get_event',{event_id:this.event_id}).then(result=> {
        this.setAttribute('event',result)
    });
  }

  loadContacts() {
    resource.action('get_uninvited_contacts', { event_id: this.event_id }).then(result => {
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
    this.setAttribute('sending',true)
    resource.action('send_invitations', { 
      event_id: this.event_id,
      contact_ids: this.selectedContacts
    }).then(result => {
        toast$.display(result)
      if (result.success) {
        this.setAttribute('invitationSent', true)
        this.loadContacts()
        toast$.display(result)
      }
      this.setAttribute('sending',false)
    })
  }
}