<?php namespace Metayota; include_once('../metayota/library.php');   include_once('../translation-service/translation-service.php');  ?><div class="org-diagram">
  <div if="this.loading" class="loading">Loading…</div>
  <div if="this.error" class="error">{this.error}</div>
  <div if="this.diagram" class="chart-container" element="chartContainer"></div>
</div>
