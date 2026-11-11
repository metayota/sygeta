class MailSender extends Tag {
    setup() {
        this.sending = false;
        this.error = null;
        this.success = false;
        this.subject = '';
        this.recipients = [];
        this.messageBody = '';
        this.attachments = [];
        this.fetchContacts();
        this.loadRecipients();
    }

    fetchContacts() {
        resource.action('get_contacts').then(result=> {
            this.setAttribute('contactOptions', result)
        })
    }

    // Getters and setters for parameters
    get receiver() {
        return this._receiver;
    }

    get subject() {
        return this._subject;
    }

    set message(m) {
        this.text = m
    }

    get text() {
        return this._text;
    }

    get is_html() {
        return this._is_html;
    }

    get template_name() {
        return this._template_name;
    }

    get template_vars() {
        return this._template_vars;
    }


    get user_id() {
        return this._user_id;
    }

    set subject(v) {
        this._subject = v;
        this.update('this.subject');
    }

    set text(v) {
        this._text = v;
        this.messageBody = v;
        this.update('this.messageBody');
    }

    set is_html(v) {
        this._is_html = v;
        this.update('this.is_html');
    }

    set template_name(v) {
        this._template_name = v;
        this.update('this.template_name');
    }

    set template_vars(v) {
        this._template_vars = v;
        this.update('this.template_vars');
    }

    set attachments(v) {
        this._attachments = v;
        this.update('this.attachments');
    }

    get attachments() {
        return this._attachments
    }

    set recipients(v) {
        this._recipients = v;
        this.update('this.recipients');
    }

    set user_id(v) {
        this._user_id = v;
        this.update('this.user_id');
    }

    async loadRecipients() {
        this.recipientsList = [];
        this.update('this.recipientsList');
    }

    showError(message) {
        this.error = message;
        this.success = false;
        this.update('this.error');
        this.update('this.success');
    }

    showSuccess(message) {
        this.error = null;
        this.success = true;
        this.update('this.error');
        this.update('this.success');
    }

    validateForm() {
        if (!this.subject?.trim()) {
            this.showError('Subject is required');
            return false;
        }
        if (!this.recipients?.length == 0) {
            this.showError('Recipient is required');
            return false;
        }
        if (!this.tinymce.value?.trim()) {
            this.showError('Message is required');
            return false;
        }
        return true;
    }

    async sendEmail() {
        if (!this.validateForm()) return;

        try {
            this.sending = true;
            this.error = null;
            this.success = false;
            this.update('this.sending');
            this.update('this.error');
            this.update('this.success');
            let mailData = {
                subject: this.subject,
                recipients: this.recipientsEl.value,
                message: this.tinymce.value,
                attachments: this.attachments
            }
            const response = await resource.action('send-mail', mailData);

            toast$.display(response)
            if (response.success) {
                this.showSuccess('Email sent successfully');
                this.resetForm();
            } else {
                this.showError(response.message || 'Failed to send email');
            }
            this.fire('sent',mailData)
        } catch (error) {
            this.showError(error.message || 'Failed to send email');
        } finally {
            this.sending = false;
            this.update('this.sending');
        }
    }

    resetForm() {
        this.subject = '';
        this.messageBody = '';
        this.attachments = [];
        this.recipientsEl.value = []
        this.update('this.subject');
        this.update('this.messageBody');
        this.update('this.attachments');
    }
}