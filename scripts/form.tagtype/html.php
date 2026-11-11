<?php namespace Metayota; include_once('../metayota/library.php');   include_once('../translation-service/translation-service.php');  ?><div class="form-tagtype">
    <dropdown label="{this.label ? this.label : '<?= translate('resource_type') ?>'}" (change)="this.updated(event)" label="{this.label}" name="{this.name}" options="{this.filteredTags}" value="{this.value}"></dropdown>
</div>