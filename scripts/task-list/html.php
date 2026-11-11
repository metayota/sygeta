<?php namespace Metayota; include_once('../metayota/library.php');  ?><div class="task-list">
    <h2><?= translate('current_tasks') ?></h2>
    <p if="this.openTasks?.length == 0"><?= translate('no_open_tasks') ?></p>
    <div class="task-section current-tasks" if="this.openTasks?.length > 0">
        <div for="task of this.openTasks">
            <div class="task">
                <div class="task-header">
                    <h3>{task.title}</h3>
                    <div class="task-actions">
                        <button (click)="this.completeTask(task)" class="complete-btn"><?= translate('complete') ?></button>
                        <button (click)="this.hideTask(task)" class="hide-btn"><?= translate('hide') ?></button>
                    </div>
                </div>
                <p class="task-priority"><?= translate('priority') ?>: {task.priority}</p>
                <p class="task-description">{task.description}</p>
            </div>
        </div>
    </div>

    <h2 if="this.completedTasks?.length > 0"><?= translate('recently_completed_tasks') ?></h2>  
    <div class="task-section completed-tasks">
        <div for="task of this.completedTasks">
            <div class="task completed">
                <div class="task-header">
                    <h3>{task.title}</h3>
                    <div class="task-actions">
                        <button (click)="this.hideTask(task)" class="hide-btn"><?= translate('hide') ?></button>
                    </div>
                </div>
                <p class="task-description">{task.description}</p>
            </div>
        </div>
    </div>
    <div if="this.mode != 'new_task'">
        <form.button (click)="this.setAttribute('mode', 'new_task')" label="<?= htmlentities(translate('new_task')) ?>"></form.button> 
    </div>
    <div if="this.mode == 'new_task'">
        <form.resource (cancel)="this.setAttribute('mode', 'normal')" cancellabel="<?= htmlentities(translate('cancel')) ?>" submitlabel="<?= htmlentities(translate('submit_task')) ?>" hide_parameters="{ ['user_id','hidden','completed'] }" resourcetype="task" (submit)="this.addTask(event)"></form.resource>
    </div>
</div>