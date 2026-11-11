<?php namespace Metayota; include_once('../metayota/library.php');  ?><span class="view-file">
    <?php if(getConfig('wordpress')): ?>
        <img if="this.isImage()" src="<?= getConfig('wordpress') ? '{this.value}' : '/call/image.resize?w=200&format=webp&src=/uploads/{this.folder}/{this.value}' ?>"/><br/>
        <a if="!this.isImage()" target="_blank" href="{this.value}">{this.value}</a>
    <?php else: ?>
        <a  if="this.isImage()" target="_blank" href="{this.getPath()}"><img src="{this.getPathWithResize()}"/><br/></a>
        <a if="!this.isImage()" target="_blank" href="">{this.value}</a>
    <?php endif; ?>
</span>