<?php namespace Metayota; include_once('../metayota/library.php');   include_once('../translation-service/translation-service.php');  ?><div if="this.status != 'not_found' && this.status != 'loading'">
    <h1 if="!!this.title_translated && this.show_title">{this.title_translated}</h1>
    <include tag="{this.resourcetype}" attributes="{this.options}"></include>
</div>
<when test="{router$.params.tab || router$.params.page}" then="this.getPage()"></when>
<div if="this.status == 'not_found'">
    <include element="includeEl" (loaded)="this.pageLoaded(event)" tag="{this.included_page}"></include>
</div>
