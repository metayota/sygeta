<?php namespace Metayota; include_once('../metayota/library.php');  ?><div class="form-date">
    <form.text label="{this.label}" value="{this.htmlValue}" (change)="this.dateChanged(event)" type="{this.type}"></form.text>
    <p><t>your_timezone</t>: {this.userTimezone}</p>
    <p class="error-message" if="!!this.errorMessage">{this.errorMessage}</p>
</div>