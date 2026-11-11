class MailReaderTag extends Tag {
    init() {
        this.message = {};
        if (router$.params.tab) {
            this.loadMessage(router$.params.tab);
        }
    }

    async loadMessage(id) {
        try {
            const result = await resource.call({
                command: 'get_message',
                id: id
            });
            this.message = result;
            this.update('this.message');
            
            if (this.message.message_html) {
                this.loadMessageContent();
            }
        } catch (error) {
            console.error('Error loading message:', error);
        }
    }

    loadMessageContent() {
        const doc = this.mailFrame.contentDocument;
        doc.open();
        doc.write(this.message.message_html);
        doc.close();
    }

    formatDate(dateString) {
        const date = new Date(dateString);
        return date.toLocaleString();
    }
}