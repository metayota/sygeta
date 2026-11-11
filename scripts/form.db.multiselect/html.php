<?php namespace Metayota; include_once('../metayota/library.php');  ?><div>
    <dropdown element="dropdownEl" multiple="{true}" options="{this.options}" value="{this.value}" (change)="this.selectionChanged(event)" label="{this.label}"></dropdown>
</div>