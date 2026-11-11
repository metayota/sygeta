<?php namespace Metayota; include_once('../metayota/library.php');   include_once('../translation-service/translation-service.php');  ?><div class="news-list">
  <h1><?= translate('news_list_title') ?></h1>
  <div if="this.news?.length > 0" for="news of this.news" >
    <div class="news-item">
        <h3>{news.title}</h3>
        <p class="teaser">{news.teaser}</p>
        <p class="meta">
        <?= translate('news_author') ?> {news.author} <?= translate('news_publish_date') ?> {news.publish_date_formatted}
        </p>
        <a link="/news-detail/{news.id}" class="read-more"><?= translate('read_more') ?></a>
    </div>
  </div>
  <p if="this.news?.length == 0"><t>no_news_available</t></p>
</div>