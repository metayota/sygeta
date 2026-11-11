<div class="form-signature form-box {this.class} {!!this.errorMessage ? 'has-error' : ''}"> 
	<label if="this.label !== undefined" class="form-label">{translate(this.label)}</label>
	<canvas element="signatureCanvas" class="signature-canvas"></canvas>
    <div class="signature-buttons">
        <a (click)="this.resetSignature()" class="form-button small-button"><t>reset_signature</t></a>
        <a (click)="this.signDocument()" class="form-button small-button"><t>sign_document</t></a>
    </div>
</div>
<p><t>signature_description</t></p>
<p class="error-message" if="!!this.errorMessage">{this.errorMessage}</p>