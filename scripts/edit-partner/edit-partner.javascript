class EditPartner extends Tag {
    init() {
        this.title = 'edit_partner_title';
        this.partner = {};
        this.loadPartner();
    }
    
    async loadPartner() {
        const id = Navigation.getCurrentId();
        this.partner = await resource.action('load', {id});
        this.update('partner');
    }
    
    async update(data) {
        try {
            await resource.action('update', {
                ...data,
                id: Navigation.getCurrentId()
            });
            Notification.success('partner_updated_success');
            Navigation.goto('/admin-partners');
        } catch(error) {
            Notification.error('partner_update_failed');
        }
    }
}