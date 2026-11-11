class ContactCard extends Tag {
    set contact_id(c) {
        this._contact_id = c
        if (c) {
            this.loadContact()
        }
    }

    get contact_id() {
        return this._contact_id
    }

    loadContact() {
        resource.action('get_contact', { id: this.contact_id }).then(result => {
            this.setAttribute('contact', result)
        })
    }
    cleanedPhoneNumber(rawPhone) {
        if (!rawPhone) {
            return undefined
        }
        // Trim the input first
        rawPhone = rawPhone.trim();

        // Match a leading + if it exists
        const hasPlus = rawPhone.startsWith('+');

        // Remove everything except digits
        let cleaned = rawPhone.replace(/[^\d]/g, '');

        // Re-add the + if it was originally at the start
        if (hasPlus) {
            cleaned = '+' + cleaned;
        }

        return `tel:${cleaned}`;
    }

    formatAddress(contact) {
        if (!contact) return '';

        const { street, zip_code, city } = contact;
        const parts = [street, zip_code, city].filter(part => part && part.trim() !== '');
        return parts.length > 0 ? parts.join(' ') : '';
    }


}