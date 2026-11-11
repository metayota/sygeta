<div class="view-contract" if="this.contract">
    <innerhtml value="{this.contract.content_translated}"></innerhtml>
    <div if="!this.contract.signed && !this.loading">
        <signature-pad (signed)="this.signDocument(event)" label="<t>view_contract_signature_label</t>"></signature-pad>
    </div>
    <loader loading="{this.loading}"></loader>
</div>