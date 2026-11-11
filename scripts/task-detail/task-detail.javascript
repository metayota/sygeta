class TaskDetail extends Tag {
  init() {
    this.task_id = router$.params.tab
    this.loadTask();
  }
  
  loadTask() {
    resource.action('get_task', { id: this.task_id }).then(result => {
      this.setAttribute('task', result);
    });
  }
  
  markTaskComplete() {
    resource.action('mark_task_complete', { id: this.task_id }).then(result => {
      toast$.show('Task marked as complete');
      this.loadTask();
    });
  }
  
  hideTask() {
    resource.action('hide_task', { id: this.task_id }).then(result => {
      toast$.show('Task hidden');
      router$.goto('/task-list');
    });
  }
}