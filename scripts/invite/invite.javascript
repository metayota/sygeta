class InviteFriends extends Tag {
    setup() {

    }

    init() {
        let view = router$.params.tab;
        this.setView(view)
        this.generateInviteLinks()
        
    }

    submit() {
        if (this.view == 'addressbook') {
             
            let selectedContacts = Object.keys(this.selectedContacts).filter(key => this.selectedContacts[key] === true);
            resource.action('send_mail',{contacts:selectedContacts, mail_version:this.mail_version, personal_message:this.personal_message}).then(result=> {
                this.loadContacts()
                toast$.show(result.message,true,result.type)
            })
        } else {
            GlobalResource.call('upload-addressbook',{'email_addresses':this.mail_addresses_element.value}).then(result=> {
                resource.action('send_mail',{contacts:result.contact_ids, mail_version:this.mail_version, personal_message:this.personal_message}).then(result=> {
                    toast$.show(result.message,true,result.type)
                })
            })
            
        }
    }

    setView(v) {
        this.setAttribute('view',v)
        if (v == 'email') {
            this.setMailVersion('1')
        }
        if (v == 'addressbook') {
            this.loadContacts()
            this.selectedContacts = []
            this.setMailVersion('1')
        }
    }

    checkedContact(contact_id, send) {
        this.selectedContacts[contact_id] = send
    }

    loadContacts() {
        resource.action('get_contacts').then(result=> {
            this.setAttribute('contacts', result) 
        })
    
    }

    otherShareText() {
        
        resource.action('get_share_text',{'share_link':this.invite_links[0]}).then(result=> {
            this.setAttribute('share_text',result.share_text)
            this.setAttribute('copied_to_clipboard', false)
        })
    }

    copyToClipboard() {
        navigator.clipboard.writeText(this.share_text).then(() => {
                this.setAttribute('copied_to_clipboard', true)
                window.setTimeout(() => {
                    this.setAttribute('copied_to_clipboard', false)
                },10000)
            })
            .catch(err => {
                console.log('Could not copy text: ', err);
            });
    }

    addContact(contact) {
        DialogForm.createDialogWithData('invite_contact',contact).then(result=> {
            resource.action('add_contact',contact).then(result=> {
                this.loadContacts()
            })
        })
    }
    deleteContact(contact) {
        if (confirm(translate('sure_to_delete'))) {
            resource.action('delete_contact',contact).then(result=> {
                this.loadContacts()
            })
        }
    }

    editContact(contact) {
        DialogForm.createDialogWithData('invite_contact',contact).then(result=> {
            resource.action('update_contact',result).then(result=> {
                this.loadContacts()
            })
        })
    }

    shareLink(socialMediaPage) {
        
        this.shareOn(socialMediaPage, this.invite_links[0], this.share_text);
    }

    updatePreview(personalMessage) {
        this.personal_message = personalMessage

        let personalText = translate('your_personal_message')
        if (this.personal_message != undefined && this.personal_message != '') {
            personalText = this.personal_message
        }
        this.setAttribute('mail_text',this.mail_text_template.replace('__PERSONAL_MESSAGE__',personalText))
    }

    

shareOn(socialMediaPage, url, text) {
    var shareUrl = '';
    var encodedUrl = encodeURIComponent(url);
    var encodedText = encodeURIComponent(text);

    switch(socialMediaPage) {
        case 'xcom':
            shareUrl = `https://x.com/intent/tweet?text=${encodedText}`;
            break;
        case 'whatsapp':
            shareUrl = `https://wa.me/?text=${encodedUrl}`;
            break;
        case 'telegram':
            shareUrl = `https://telegram.me/share/url?url=${encodedUrl}&text=${encodedText}`;
            break;
        case 'skype':
            shareUrl = `https://web.skype.com/share?url=${encodedUrl}&text=${encodedText}`;
            break;
        case 'reddit':
            shareUrl = `https://reddit.com/submit?url=${encodedUrl}&title=${encodedText}`;
            break;
        case 'pinterest':
            shareUrl = `http://pinterest.com/pin/create/link/?url=${encodedUrl}`;
            break;
        case 'linkedin':
            shareUrl = `https://www.linkedin.com/shareArticle?mini=true&url=${encodedUrl}&title=${encodedText}`;
            break;
       /* case 'instagram':
            alert('Instagram does not support URL sharing via web. Use their app for sharing.');
            return;
        case 'instagram_messenger':
            alert('Instagram Messenger does not support URL sharing via web. Use their app for sharing.');
            return;*/
        case 'facebook':
            shareUrl = `https://www.facebook.com/sharer.php?u=${encodedUrl}&quote=${encodedText}`;
            break;
        default:
            if (navigator.share) {
                navigator.share({
                    title: 'Alterworld',
                    text: text,
                    url: url,
                })
                .then(() => console.log('Successful share'))
                .catch((error) => console.log('Error sharing', error));
            } else {
                // Fallback for browsers that do not support navigator.share
                toast$.show(translate('only_on_android_and_ios'))
            }
            return;
    }

    window.open(shareUrl, '_blank', 'toolbar=0,status=0,width=626,height=436');
}

    setSubview(v) {
        this.setAttribute('subview',v)
    }

    generateInviteLinks() {
        resource.action('generate_invitation_links').then(result=>{
            this.setAttribute('invite_links', result)
            if (this.share_text == undefined) {
                this.otherShareText()
            }
        })
    }

    extractAndSortEmails(inputString) {
    // Erweiterter regulärer Ausdruck, um E-Mail-Adressen zu finden
    let emailRegex = /(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@(([^<>()[\]\\.,;:\s@\"]+\.)+[^<>()[\]\\.,;:\s@\"]{2,})/gi;

    // Finden Sie alle E-Mail-Adressen im Text
    let emails = inputString.match(emailRegex);

    if (emails) {
        // Sortieren Sie die E-Mail-Adressen alphabetisch
   //     emails.sort();
        return emails.join(', ');
    } else {
        // Wenn keine E-Mail-Adressen gefunden wurden, geben Sie eine leere Liste zurück
        return [];
    }
}

    mailsUpdated(value) {
        let mailAdresses = this.extractAndSortEmails(value)
        if (!this.mail_addresses_element.value.startsWith(mailAdresses)) {
            this.setAttribute('mail_addresses', mailAdresses)
        }
    }

    setMailVersion(version) {
        this.setAttribute('mail_version',version)
        resource.action('get_invite_text_preview',{version:version}).then(result=> {
            this.setAttribute('mail_subject',result.subject)
            let personalText = translate('your_personal_message')
            if (this.personal_message != undefined && this.personal_message != '') {
                personalText = this.personal_message
            }
            this.setAttribute('mail_text',result.text.replace('__PERSONAL_MESSAGE__',personalText))
            this.setAttribute('mail_text_template',result.text)
        })
    }
}