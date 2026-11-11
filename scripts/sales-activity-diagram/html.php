<?php namespace Metayota; include_once('../metayota/library.php');   include_once('../translation-service/translation-service.php');  ?><div class="line-diagram-container">
    <div class="diagram-header">
        <h3 class="diagram-title">{this.diagram?.title_translated}</h3>
        <p class="diagram-description">{this.diagram?.description_translated}</p>
    </div>

    <loader loading="{this.loading}"></loader>

    <div if="this.error" class="error-message">
        <t>diagram_error</t>: {this.error}
    </div>

    <div class="diagram-content">
        <div element="chartContainer" class="chart-container"></div>
    </div>
</div>
