<?php namespace Metayota; include_once('../metayota/library.php');  ?><div class="dialog {this.class} {this.visible then 'visible'}" (keydown)="this.keyPressed(event)">
    <div class="dialog-bg" if="this.visible == true" ></div>
	<div tabindex="1" (keydown)="this.keyPressed(event)"  class="modal-window" element="dialogModal" if="this.visible == true" (keypress)="this.keyPressed(event)">
		<div class="modal-header">
            <div (click)="this.hide()" class="modal-close"><img src="<?= getConfig('wordpress') ? '/wp-content/plugins/metayota/editor_scripts' : '/scripts' ?>/rc.icon/close.svg" width="21" height="21"/></div>
        </div>
		<div if="this.resourcetype != undefined || this.display_tag != undefined" class="modal-content">
            <div if="this.title != undefined">
                <h2>{this.title}</h2>
            </div>
            <div if="this.display_tag === undefined">
                <form.resource autocomplete="{this.autocomplete}" hide_parameters="{this.hide_parameters}" showdocumentation="{this.show_description}" element="form" (submit)="this.submit(event)" value="{this.tagdata}" label="{this.label}" resourcetype="{this.resourcetype}" submitlabel="{this.submitlabel != null ? this.submitlabel : 'OK'}"></form.resource>
            </div>
            <div if="this.display_tag != undefined">
                <include (submit)=" this.submit(event)" element="includedTag" tag="{this.display_tag}" attributes="{this.tagdata}"></include>
            </div>
		</div>
    </div>
</div>