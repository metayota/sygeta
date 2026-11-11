class CloseDeal extends Tag {
    init() {
        this.closed_deal_id = router$.params.tab;
        this.selectedPartner = null;
        if (this.closed_deal_id) {
            this.fetchExistingDeal()
            this.fetchCommissionOverview();
        }
    }

    fetchExistingDeal() {
        resource.action('get_deal', {closed_deal_id:this.closed_deal_id}).then(result=> {
            this.setAttribute('deal', result)
        })
    }

    closeDeal(data) {
        resource.action('submit_deal', data).then(result => {
           // this.setAttribute('commissionOverview', result);
            toast$.display(result);
            if (result.inserted) {
                router$.goto('/close-deal/' + result.closed_deal_id);
            } else {
                this.fetchCommissionOverview();
            }
        });
    }

    bookDeal() {
        resource.action('book_deal', {closed_deal_id:this.closed_deal_id}).then(result=> {
            toast$.display(result)
            this.fetchCommissionOverview()
        })
    }

    fetchCommissionOverview() {
        resource.action('get_commission_overview', {closed_deal_id: this.closed_deal_id}).then(result => {
            this.commissionOverview = result;
            this.update('this.commissionOverview');
        });
    }

    selectPartner(commission) {
        this.selectedPartner = this.selectedPartner?.partner_id === commission.partner_id ? null : commission;
        this.update('this.selectedPartner');
    }

    set commissionOverview(data) {
        this._commissionOverview = data;
        this.update('this.commissionOverview');
    }

    get commissionOverview() {
        return this._commissionOverview;
    }
}