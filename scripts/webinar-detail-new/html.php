<?php namespace Metayota; include_once('../metayota/library.php');  ?><div class="mdc-card webinar-detail">
  <div class="mdc-card__primary-action" if="!!this.webinar">
    <div class="mdc-card__media mdc-card__media--16-9" style="background-image: url('{this.webinar.image}');"></div>
    <div class="webinar-content">
      <h2 class="mdc-typography--headline4">{this.webinar.title}</h2>
      <h3 class="mdc-typography--subtitle1">{this.webinar.date}, {this.webinar.time}</h3>
      <div class="mdc-typography--body1">{this.webinar.description}</div>
      <div class="webinar-meta mdc-typography--body2">
        <span class="attendees">{this.webinar.attendees_count} <?= translate('attending') ?></span>
      </div>
    </div>
  </div>
  <div class="mdc-card__actions">
    <div class="mdc-card__action-buttons">
      <button class="mdc-button mdc-card__action mdc-card__action--button mdc-ripple-surface" (click)="this.invite()">
        <span class="mdc-button__label"><?= translate('invite') ?></span>
      </button>
      <button class="mdc-button mdc-card__action mdc-card__action--button mdc-ripple-surface" (click)="this.register()">
        <span class="mdc-button__label"><?= translate('register') ?></span>
      </button>
    </div>
  </div>
</div>