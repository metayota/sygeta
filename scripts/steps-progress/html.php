<?php namespace Metayota; include_once('../metayota/library.php');   include_once('../translation-service/translation-service.php');  ?><div>
    <div class="pipeline-steps" for="step of this.steps">
        <div class="pipeline-step">
            <div class="step-{step.completed ? 'completed' : 'open'}"></div>
            <div (click)="this.clickedStep(step)" class="{step.link ? 'has-link' : ''}">{step.title_translated}</div>
        </div>
    </div>
</div>