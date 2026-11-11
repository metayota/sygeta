class SalesDocumentDetailView extends Tag {
    init() {
        this.document = null;
        this.items = [];
        this._loadTimeout = null;

        if (router$.params?.page == 'sales-document-detail-view') {
            this.document_id = router$?.params?.subpage ? router$?.params?.subpage : router$?.params?.tab;
        }
        if (window.location.href.includes('is_export')|| window.location.hash.includes('is_export') ) {
            this.setAttribute('isExport', true)
        } else {
            this.setAttribute('isExport', false)
       //     this.refresh()
        }
    }

    convert() {
        router$.goto('/convert-sales-document/'+this.document_id)
    }

    nl2p(text) {
        return text
            .split(/\n/)
            .filter(line => line.trim() !== '')
            .map(line => `<p>${line.trim()}</p>`)
            .join('');
    }

    set document_id(d) {
        if (d) {
            this._document_id = d;
            this.scheduleLoadDocument();
        }
    }

    get document_id() {
        return this._document_id;
    }

    set contact_id(c) {
        if (c) {
            this._contact_id = c;
            this.scheduleLoadDocument();
        }
    }

    get contact_id() {
        return this._contact_id;
    }

    nl2br(text) {
        return text.replace(/\n/g, '<br>');
    }

    sendCoverMail() {
        if (confirm(SalesDocumentDetailView.msg_confirm_send_cover_mail)) {
            let sales_document_id = this.document_id
            this.setAttribute('sendingCoverMail', true)
            
            this.refresh().then(refreshResult => {
                resource.action('send_cover_mail', {sales_document_id}).then(result=> {
                    toast$.display(result)
                    this.loadDocument()
                })
            })
        }
    }

    scheduleLoadDocument() {
        if (this._loadTimeout) {
            clearTimeout(this._loadTimeout);
        }

        this._loadTimeout = window.setTimeout(() => {
            this.loadDocument();
            this._loadTimeout = null;
        }, 0);
    }

    loadDocument() {
        if (!this.document_id) return;
        resource.call({
            id: this.document_id,
            contact_id: this.contact_id
        }).then(result => {
            this.setAttribute('documentType', result.documentType)
            this.document = result.document;
            this.items = result.items;
            this.all_quantity_one = this.items.every(item => item.quantity === 1);
            this.all_tax_zero = this.items.every(item => item.tax_rate === 0 || item.tax_rate === 0.00 || item.tax_rate === '0' || item.tax_rate === '0.00');
            this.contact = result.contact;
            this.company = result.company;
            this.totals = result.totals;
            this.update('this.items');
            this.update('this.document');
            this.update('this.contact');
            this.update('this.company');
            this.update('this.totals');
            this.setAttribute('sendingCoverMail', false)
            if (!this.isExport) {
             //   this.refresh()
            }
        });
    }

    refresh() {
        
    
        this.setAttribute('refreshing',true)
        return resource.action('export', {
            id: this.document_id
        }).then(result => {
            this.loadDocument()
       //     this.setAttribute('refreshing',false)
        });
    
    }

    exportPdf() {
        resource.action('export', {
            id: this.document_id
        }).then(result => {
            window.open(result.pdf_path, '_blank');
        });
    }

    getTotal(item) {
        const netAmount = item.quantity * item.unit_price;
        const taxAmount = netAmount * (item.tax_rate / 100);
        const total = netAmount + taxAmount;
        return total;
    }
}