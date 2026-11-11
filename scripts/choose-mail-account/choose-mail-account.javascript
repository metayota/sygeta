class ChooseMailAccount extends Tag {
    init() {
        this.loading = true;
        this.emailAccounts = [];
        this.loadEmailAccounts();
    }

    set label(value) {
        this._label = value;
        this.update('this.label');
    }

    get label() {
        return this._label;
    }

    set selected_account(value) {
        this._selected_account = value;
        this.update('this.selected_account');
    }

    get selected_account() {
        return this._selected_account;
    }

    set multiple(value) {
        this._multiple = value;
        this.update('this.multiple');
    }

    get multiple() {
        return this._multiple;
    }

    set change(value) {
        this._change = value;
        this.update('this.change');
    }

    get change() {
        return this._change;
    }

    async loadEmailAccounts() {
        try {
            this.loading = true;
            this.update('this.loading');

            const result = await resource.action('load', {});
            
            if (result.status === 'success') {
                this.emailAccounts = result.data;
                this.update('this.emailAccounts');
                this.fire('loaded',this.emailAccounts)
            } else {
                toast$.show(result.message, false);
            }
        } catch (error) {
            toast$.show(error.message, false);
        } finally {
            this.loading = false;
            this.update('this.loading');
        }
    }

    openEmailClient(accountId) {
        this.fire('change',accountId)
        this.selected_account = accountId;
        router$.goto('/email-client/' + accountId);
    }
}