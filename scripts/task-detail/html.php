<?php namespace Metayota; include_once('../metayota/library.php');  ?>
<div class="task-detail" if="!!this.task">
  <h2>{this.task.title}</h2>
  <p class="task-description">{this.task.description}</p>
  <div class="task-priority">
    Priority: <span class="priority-{this.task.priority}">{this.task.priority}</span>
  </div>
  <div class="task-actions">
    <div if="!this.task.completed">
        <form.button label="Mark as Complete" icon="check" (click)="this.markTaskComplete()"></form.button>
    </div>
    <form.button label="Hide Task" icon="visibility_off" (click)="this.hideTask()"></form.button>
  </div>
</div>