class TaskList extends Tag {
    init() {
        this.loadTasks();
    }

    loadTasks() {
        resource.action('get_tasks').then(result => {
            this.setAttribute('tasks', result);
            this.setAttribute('openTasks',result.filter(task => !task.completed && !task.hidden))
            this.setAttribute('completedTasks',result.filter(task => task.completed && !task.hidden))
        });
    }

    completeTask(task) {
        resource.action('complete_task', {id: task.id}).then(() => {
            this.loadTasks();
        });
    }

    hideTask(task) {
        resource.action('hide_task', {id: task.id}).then(() => {
            this.loadTasks();
        });
    }

    addTask(task) {
        resource.action('create_task',task).then(result=> {
            toast$.display(result)
            this.loadTasks()
        })
        this.setAttribute('mode', 'normal')
    }
}