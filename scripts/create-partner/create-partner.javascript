class CreatePartner extends Tag {
    init() {
        this.title = 'create_partner_title';
        this.formData = {};
    }
    
    async create(data) {
        try {
            const result = await resource.action('create', data);
            Notification.success('partner_created_success');
            Navigation.goto('/admin-partners');
        } catch(error) {
            Notification.error('partner_creation_failed');
        }
    }
}