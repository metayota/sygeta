<?php namespace Metayota; include_once('../metayota/library.php');   include_once('../translation-service/translation-service.php');   include_once('../mail-helper/mail-helper.php');  ?><h1><t>book_object_presentation</t></h1>

<div class="infobox" if="this.possible === false">
    <img src="/images/icons/warning-sign.svg"/>
    <p><b>{this.possibleMessage}</b></p>
</div>

<div class="presentations-booked" for="bookedPresentation of this.bookedPresentations">
    <div class="icon-text">
        <img src="/images/icons/check-all.svg" />
        <span>{bookedPresentation.text}</span>
    </div>
        
</div>

<div if="this.bookedPresentations && this.bookedPresentations.length == 0" class="{this.possible === false ? 'disabled-element' : ''}">

    <dropdown element="propertyEl" label="<?= translate('property') ?>"  options="{this.properties}" label=""></dropdown>

    <dropdown element="dateEl" label="<?= translate('date') ?>" (change)="this.pickedDate(event)" sort="{false}" options="{this.dates}"></dropdown>

    <dropdown element="timeEl" value="{this.timeValue}" label="<?= translate('time') ?>"  sort="{false}" options="{this.times}"></dropdown>

    <form.button (click)="this.book()" label="<?= translate('book_object_presentation_btn') ?>"></form.button>
</div>