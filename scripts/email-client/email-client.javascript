class EmailClient extends Tag {
    setup() {
        // Initialize state properties
        this._email = '';
        this._imap_host = '';
        this._imap_port = 993;
        this._imap_username = '';
        this._imap_password = '';
        this._use_ssl = true;
        this._folder_list = [];
        this._messages_per_page = 25;
        this._refresh_interval = 300;
        this._account_id = router$.params.tab;

        // UI state
        this.loading = false;
        this.error = null;
        this.folders = [];
        this.emails = [];
        this.selectedFolder = null;
        this.selectedEmail = null;
        this.currentPage = 1;
        this.totalPages = 1;
    }

    // Getters and setters for parameters
    get email() { return this._email; }
    set email(v) {
        this._email = v;
        this.update('this.email');
    }

    get imap_host() { return this._imap_host; }
    set imap_host(v) {
        this._imap_host = v;
        this.update('this.imap_host');
    }

    get imap_port() { return this._imap_port; }
    set imap_port(v) {
        this._imap_port = v;
        this.update('this.imap_port');
    }

    get imap_username() { return this._imap_username; }
    set imap_username(v) {
        this._imap_username = v;
        this.update('this.imap_username');
    }

    get imap_password() { return this._imap_password; }
    set imap_password(v) {
        this._imap_password = v;
        this.update('this.imap_password');
    }

    get use_ssl() { return this._use_ssl; }
    set use_ssl(v) {
        this._use_ssl = v;
        this.update('this.use_ssl');
    }

    get folder_list() { return this._folder_list; }
    set folder_list(v) {
        this._folder_list = v;
        this.update('this.folder_list');
    }

    get messages_per_page() { return this._messages_per_page; }
    set messages_per_page(v) {
        this._messages_per_page = v;
        this.update('this.messages_per_page');
    }

    get refresh_interval() { return this._refresh_interval; }
    set refresh_interval(v) {
        this._refresh_interval = v;
        this.update('this.refresh_interval');
    }

    get account_id() { return this._account_id; }
    set account_id(v) {
        this._account_id = v;
        this.update('this.account_id');
        this.loadFolders();
        this.loadEmails()
    }

    init() {
        this.loadFolders();

        // Set up refresh interval
        if (this.refresh_interval) {
            setInterval(() => this.refreshCurrentView(), this.refresh_interval * 1000);
        }

        // Listen for keydown events
        window.addEventListener('keydown', (event) => {
            if ((event.key === 'Delete' || event.key === 'Backspace') && this.selectedEmail) {
                event.preventDefault(); // Prevent browser navigation on Backspace
                this.deleteSelectedEmail();
            }
        });
    }

    markAsSpam(email) {

        this.loading = true;
        this.update('this.loading');

        resource.action('mark-spam', {
            account_id: this._account_id,
            message_id: email.message_id,
            message_num: this.selected,
            folder: this.selectedFolder.name
        }).then(result => {
            toast$.show(result.message);
            // Refresh current folder after marking as spam
            this.loadEmails();
            this.loadFolders();
        }).catch(error => {
            this.error = error.message;
            this.update('this.error');
            toast$.show(error.message, false);
        }).finally(() => {
            this.loading = false;
            this.update('this.loading');
        });
    }
    deleteSelectedEmail() {
        if (!this.selectedEmail) {
            toast$.show("No email selected.", false);
            return;
        }

        const isInTrash = this.selectedFolder.name.toLowerCase() === 'trash';

        // Function to finalize deletion and refresh view
        const finalizeDeletion = (message) => {
            toast$.show(message);
            this.selectedEmail = null;
            this.update('this.selectedEmail');
            this.loadEmails();
            this.loadFolders();
        };

        this.loading = true;
        this.update('this.loading');

        if (isInTrash) {
            // Permanently delete email from Trash
            resource.action('delete-email', {
                account_id: this._account_id,
                message_id: this.selectedEmail.message_id,
                message_num: this.selectedEmail.message_num,
                folder: this.selectedFolder.name
            })
                .then(result => finalizeDeletion(result.message))
                .catch(error => {
                    this.error = error.message;
                    this.update('this.error');
                    toast$.show(error.message, false);
                })
                .finally(() => {
                    this.loading = false;
                    this.update('this.loading');
                });
        } else {
            // Move email to Trash
            const trashFolder = this.folders.find(f => f.name.toLowerCase() === 'trash');
            if (!trashFolder) {
                toast$.show("Trash folder not found.", false);
                this.loading = false;
                this.update('this.loading');
                return;
            }
            resource.action('move-to-trash', {
                account_id: this._account_id,
                message_id: this.selectedEmail.message_id,
                message_num: this.selected,
                folder: this.selectedFolder.name
            })
                .then(result => finalizeDeletion(result.message))
                .catch(error => {
                    this.error = error.message;
                    this.update('this.error');
                    toast$.show(error.message, false);
                })
                .finally(() => {
                    this.loading = false;
                    this.update('this.loading');
                });
        }
    }

    accountsLoaded(event) {
        if (event.length > 0) {
            this.account_id = event[0].id
        } else {
            this.setAttribute('noAccounts', true)
        }
    }
    moveFolder(email, folder) {

        this.loading = true;
        this.update('this.loading');

        resource.action('move-folder', {
            account_id: this._account_id,
            message_id: email.message_id,
            message_num: this.selected,
            folder: this.selectedFolder.name,
            target_folder: folder
        }).then(result => {
            toast$.show(result.message);
            // Refresh current folder after marking as spam
            this.loadEmails();
            this.loadFolders();
        }).catch(error => {
            this.error = error.message;
            this.update('this.error');
            toast$.show(error.message, false);
        }).finally(() => {
            this.loading = false;
            this.update('this.loading');
        });
    }

    // New methods
    forwardEmail(email) {
        if (!email) return;

        const forwardContent = `
            <br><br>
            -------- Forwarded Message --------<br>
            From: ${email.from.name} <${email.from.email}><br>
            Date: ${email.date}<br>
            Subject: ${email.subject}<br>
            <br>
            ${email.content.html}
        `;

        // Open mail-sender component with forwarded content
        this.setAttribute('replyTo', {
            subject: `Fwd: ${email.subject}`,
            content: {
                quoted_html: forwardContent
            }
        });
        this.update('this.replyTo');
    }

    replyToMail(email) {
        email.subject = 'Re: ' + email.subject
        this.setAttribute('replyTo', email)
    }

    aiSuggestion(email) {
        resource.action('ai_answer', { email }).then(result => {
            const forwardContent = `
                ${result.ai_answer}
                <br/><br/>
                ${this.selectedEmail.content.quoted_html}
            `;

            // Open mail-sender component with forwarded content
            this.replyTo.content = {
                quoted_html: forwardContent
            }
                ;

            this.update("this.replyTo")
        })
    }

    async loadFolders() {
        if (!this.account_id) {
            return
        }
        this.loading = true;
        this.error = null;
        this.update('this.loading');
        this.update('this.error');

        try {
            const result = await resource.action('load-folders', {
                account_id: this.account_id
            });

            if (result.status === 'success') {
                this.folders = result.data[0].folders;
                this.update('this.folders');

                // Select INBOX by default if no folder selected
                if (!this.selectedFolder && this.folders.length) {
                    this.selectFolder(this.folders.find(f => f.name === 'INBOX') || this.folders[0]);
                }
            } else {
                throw new Error(result.message);
            }
        } catch (err) {
            this.error = err.message;
            this.update('this.error');
            toast$.show(err.message, false);
        } finally {
            this.loading = false;
            this.update('this.loading');
        }
    }

    async selectFolder(folder) {
        this.selectedFolder = folder;
        this.selectedEmail = null;
        this.currentPage = 1;
        this.update('this.selectedFolder');
        this.update('this.selectedEmail');
        this.update('this.currentPage');

        await this.loadEmails();
    }

    async loadEmails() {
        this.loading = true;
        this.error = null;
        this.update('this.loading');
        this.update('this.error');

        try {
            const result = await resource.action('load-emails', {
                account_id: this.account_id,
                folder: this.selectedFolder ? this.selectedFolder.name : 'INBOX',
                page: this.currentPage
            });

            if (result.status === 'success') {
                this.emails = result.data.emails;
                this.totalPages = result.data.pagination.total_pages;
                this.update('this.emails');
                this.update('this.totalPages');
                this.setAttribute('pagination', result.data.pagination)
            } else {
                throw new Error(result.message);
            }
        } catch (err) {
            this.error = err.message;
            this.update('this.error');
            toast$.show(err.message, false);
        } finally {
            this.loading = false;
            this.update('this.loading');
        }
    }

    updatePage(pageNr) {
        this.currentPage = pageNr
        this.loadEmails()
    }

    async selectEmail(email) {
        this.currentEmail = email
        this.update('this.currentEmail')
        this.replyTo = undefined
        this.update('this.replyTo')
        this.selected = email.message_num
        this.update('this.selected')
        this.update('this.emails')
        this.loading = true;
        this.update('this.loading');

        try {
            const result = await resource.action('load-email-detail', {
                message_num: email.message_num,
                account_id: this.account_id,
                message_id: email.messageId,
                folder: this.selectedFolder.name
            });

            if (result.status === 'success') {
                this.selectedEmail = result.data;
                this.update('this.selectedEmail');
            } else {
                throw new Error(result.message);
            }
        } catch (err) {
            toast$.show(err.message, false);
        } finally {
            this.loading = false;
            this.update('this.loading');
        }
    }
    async downloadAttachment(attachment) {
        try {
            // Set up fetch options to receive binary data
            const response = await resource.action('download-attachment', {
                account_id: this.account_id,
                message_id: this.selectedEmail.message_id,
                attachment_index: attachment.part_number,
                message_num: this.selected
            }, false, 'blob');

            if (response.error) {
                throw new Error(response.message);
            }

            // Check if the response is already a blob
            let blob;
            if (response instanceof Blob) {
                blob = response;
            } else {
                // If response is not a blob, create one
                blob = new Blob([response], {
                    type: response.type || 'application/octet-stream'
                });
            }

            // Create and trigger download
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.style.display = 'none';
            a.href = url;
            a.download = attachment.filename || 'download';
            document.body.appendChild(a);
            a.click();

            // Cleanup
            window.URL.revokeObjectURL(url);
            document.body.removeChild(a);

        } catch (err) {
            console.error('Download error:', err);
            toast$.show(err.message, false);
        }
    }

    refreshCurrentView() {
        if (this.selectedFolder) {
            this.loadEmails();
        }
    }
}
