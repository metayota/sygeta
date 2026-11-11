<?php namespace Metayota; include_once('../metayota/library.php');  ?><div class="news-list">
  <h2><?= translate('news_title') ?></h2>
  
  <div for="news_entry of this.news">
    <h3>{news_entry.title}</h3>
    <p>{news_entry.text}</p>
    <p><?= translate('published_on') ?> {news_entry.publish_date}</p>
  </div>

</div>