<?php namespace Metayota; include_once('../metayota/library.php');  ?>
    <h1><t>request_signature_title</t></h1>

<div if="!this.loading">
    <form.resource value="{this.formValue}" (submit)="this.requestSignature(event)" resourcetype="request-signature" submitlabel="<t>request_signature</t>"></form.resource>
</div>
<loader loading="{this.loading}"></loader>