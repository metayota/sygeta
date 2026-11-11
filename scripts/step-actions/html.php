<?php namespace Metayota; include_once('../metayota/library.php');   include_once('../translation-service/translation-service.php');  ?><div class="step-actions">
    <div if="!this.performingAction">
        <div for="stepAction of this.stepActions" class="step-actions">
            <button (click)="this.performStep(stepAction)"
                class="form-button">{stepAction.title_translated}</button>
        </div>
    </div>
    <loader loading="{this.performingAction}"></loader>
</div>