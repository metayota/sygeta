<?php namespace Metayota; include_once('../metayota/library.php');  ?><div class="form-file {this.class}"> 
	<label if="this.label !== undefined" class="form-label">{translate(this.label)}</label>
    <img if="this.isImage()" src="<?= getConfig('wordpress') ? '{this.value}' : '{this.getPathWithResize()}' ?>"/>
	<div class="btn-container" if="!this.uploading"><a class="form-button choose-file-btn" (click)="this.chooseFile()">{this.value ? this.value : this.uploadLabel}</a> <span if="this.value != undefined && this.value != ''"><form.button (click)="this.removeFile()" label="<?= htmlentities(translate('remove_file')) ?>"></form.button></span></div>
	<div if="this.uploading"><progress></progress></div>
    <p class="error-message" if="!!this.errorMessage">{this.errorMessage}</p>
</div>