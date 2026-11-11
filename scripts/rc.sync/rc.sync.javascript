class RCSync extends Tag {

    init() {
        this.syncRepositories()
    }

    syncSingleFile(resourcename) {
        if (confirm(translate('reset_update_single_file',resourcename))) {
            this.syncResource(resourcename)
        }
    }

    downloadSingleFile(resourcename) {
        if (confirm( translate('confirm_download_repository',resourcename))) {
            this.syncResource(resourcename)
        }
    }

    static syncSingleFileWithRepository(resourcename,repository) {
        return GlobalResource.action('rc.sync', 'sync_single_file',{resourcename,repository}).then(result=> {
            editor$.updateResources()
            if (result.db_update_required) {
                toast$.showCallback(undefined,'update_changed_db_table',function(){
                    Resource.call('update-database-table',{resourcetype:resourcename,tablename:resourcename}).then(result=> {
                        toast$.display(result)
                    })
                },'update_btn')
            } else if (result.db_install_required) {
                Resource.call('db.table.create',{resourcetype:resourcename,tablename:resourcename})
            } else {
                toast$.display(result)
            }
        })
    }

    syncResource(resourcename) {
        RCSync.syncSingleFileWithRepository(resourcename,this.repository).then(result=> {
            this.updateList()
        })
    }

    addRepository() {
        Resource.cls('dialog').createDialog('rc.repository', {}).then(repository => {
            resource.action('add_server', repository).then(result => {
                this.syncRepositories()
            })
        })
    }

    updateFromServer() {
        if (confirm(translate('sure_reset_all_resources'))) {
            resource.action('sync', { repository: this.repository, dosync: true }).then(result => {
                this.setAttribute('resources', result)
                this.updateList()
                editor$.updateResources()
            })
        }
    }

    set subpage(s) {
        if (s != undefined && this.subpage != s) {
            this.loadRepository(s)
        }
        
        this._subpage = s
        this.setAttribute('repository',s)   
    }

    get subpage() {
        return this._subpage
    }

    setPrivate(resource_name) {
        resource.action('set_private', {resource_name}).then(result =>{
            this.updateList();
        })
    }

    chooseRepository(repository_id) {
        router$.goto('/editor/view/rc.sync/'+repository_id)
    }

    loadRepository(repository) {
        this.repository = repository
        this.updateList()
    }

    updateList() {
        resource.action('sync', { 'repository': this.repository, 'task_id': this.task }).then(result => {
            this.setAttribute('resources', result)
        })
    }

    syncRepositories() {
        resource.action('get_repositories').then(repositories => {
            this.setAttribute('respositories', repositories)
            this.setAttribute('repositoryOptions', repositories.map(v => {
                return {
                    name: v.title,
                    value: v.id
                }
            }))
        })
    }


}