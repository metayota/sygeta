<?php namespace Metayota; include_once('../metayota/library.php');   include_once('../translation-service/translation-service.php');  ?><div class="news-detail" if="!!this.news">
  <h1>{this.news.title}</h1>
  <p class="teaser">{this.news.teaser}</p>
  <p class="meta">
    <?= translate('news_author') ?> {this.news.author} <?= translate('news_publish_date') ?> {this.news.publish_date_formatted}
  </p>
  <div class="text"><innerhtml value="{this.news.text}"></innerhtml></div>
</div>