class DatabaseTableEditor extends Tag {
    setup() {
        let setupData = resource.getData()
        this.main_server = setupData.main_server
        this.delete_link = true
        this.edit_link = setupData.edit_link
        if (!this.edit_link) {
            this.edit_link = 'popup'
        }
        this.show_description = false
        this.page = 1
        this._reloadScheduled = false
    }
    init() {
        if (App.pushBreadcrumbs) {
            App.pushBreadcrumbs()
        }
    }
    set title(t) {
        this._title = t
        this.update('this.title')
    }
    get title() {
        return this._title
    }
    set table(t) {
        this._table = t
        this.update('this.table')
        this.scheduleReloadData()
    }
    get table() {
        return this._table
    }
    set resource_name(r) {
        this.resourcetype = r
    }
    get resource_name() {
        return this.resourcetype
    }
    set hide_parameters(h) {
        if (typeof h === 'string' && h != '') {
            h = JSON.parse(h)
        }
        if (!h || !Array.isArray(h)) {
            this._hide_parameters = h;
            return;
        }
        
        this._hide_parameters = h.map(item => {
            if (typeof item === 'string') {
                return item;
            } else if (item && typeof item === 'object' && item.name) {
                return String(item.name);
            } else {
                return String(item); // fallback for other types
            }
        });
        if (this.page != undefined) {
            this.scheduleReloadData()
        }
    }
    get hide_parameters() {
        return this._hide_parameters
    }
    set where(w) {
        this._where = w
        
       // this.hide_parameters = Object.keys(w)
        if (this.page != undefined) {
            this.scheduleReloadData()
        }
    }
    isHidden(p) {
        if (p.hidden) {
            return true
        }
        if (this.show_parameters != undefined) {
            return !this.show_parameters.includes(p.name)
        }
        let hiddenParameters = this.getAllHiddenParameters()
        return hiddenParameters.includes(p.name)
    }
    get where() {
        return this._where
    }
    getSortIndicator(parameter) {
        if (this.sort == parameter && this.order != undefined) {
            if (this.order == 'ASC') {
                return '↓';
            } else {
                return '↑';
            }
        }
        return '';
    }
    countItems() {
        resource.call({table:this.table,resourcetype:this.resourcetype,count:true,search:this.search, where:this.where}).then(result => {
            this.setAttribute('count', result)
            this.setAttribute('number_of_pages', Math.ceil(result/15))
        })
    }
    pageChanged(page) {
        this.setAttribute('page', page)
        this.scheduleReloadData()
    }
    sortBy(attribute) {
        if (attribute != this.sort) {
            this.setAttribute('order','ASC')
        } else {
            if (this.order == 'DESC') {
                this.setAttribute('order','ASC')
            } else {
                this.setAttribute('order','DESC')
            }
        }
        this.setAttribute('sort',attribute)
        this.scheduleReloadData()
        this.update('this.getSortIndicator')
    }
    searchChanged(query) {
        this.search = query
        this.scheduleReloadData()
    }
    scheduleReloadData() {
        if (!this._reloadScheduled) {
            this._reloadScheduled = true;
            window.setTimeout(() => {
                this._reloadScheduled = false;
                this.reloadData();
            }, 0);
        }
    }
    reloadData() {
        if (this.table != undefined) {
            resource.call({table:this.table,resourcetype:this.resourcetype,page:this.page,sort:this.sort,order:this.order,search:this.search,where:this.where}).then(result => {
                if (result.length == 0 && this.page > 1) {
                    this.setAttribute('page',this.page-1);
                    this.scheduleReloadData();
                    return;
                }
                this.objs = result 
                this.update('this.objs')
                this.countItems()
            })
        }
    }
    set resourcetype(r) {
        if (r != undefined && this._resourcetype !== r) {
            this._resourcetype = r
            Resource.loaded(this.getServerPrefix() + r).then(resource => {
                let filteredParameters;
                if (Array.isArray(this.show_parameters)) {
                    filteredParameters = Object.keys(resource.parameters)
                        .filter(key => key === 'id' || this.show_parameters.includes(key))
                        .reduce((obj, key) => {
                            obj[key] = resource.parameters[key];
                            return obj;
                        }, {});
                } else {
                    filteredParameters = resource.parameters;
                }
                resource.parameters = filteredParameters;
                this.setAttribute('resource', resource);
                this.update('this.resourcetype');
                this.update('this.objs');
                this.update('this.resource');
            });
            this.scheduleReloadData()
        }
    }
    get resourcetype() {
        if (this._resourcetype == undefined) {
            return this._table
        }
        return this._resourcetype
    }
    deleteRow(rowid) {
        if (confirm('Are you sure you want to delete this row?')) {
            resource.action('delete', {table:this._resourcetype, row_id:rowid}).then(result => {
                this.scheduleReloadData();
            })
        }
    }
    getAllHiddenParameters() {
        let allHidden = [];
        
        // Add hide_parameters if they exist
        if (this.hide_parameters && Array.isArray(this.hide_parameters)) {
            allHidden = [...this.hide_parameters];
        }
        
        // Add where clause keys if they exist
        if (this.where && typeof this.where === 'object') {
            const whereKeys = Object.keys(this.where);
            allHidden = [...allHidden, ...whereKeys];
        }
        
        // Remove duplicates and return
        return [...new Set(allHidden)];
    }

    editRow(row) {
        if (this.edit_link && this.edit_link != 'popup') {
            let editLink = this.edit_link.replace(/\[table\]/g, this.table)
            editLink = editLink.replace(/\[id\]/g, row.id)
            editLink = editLink.replace(/\[name\]/g, row.name)
            router$.goto(editLink)
        } else {
            Resource.loaded(this.resourcetype).then( res => {
                DialogForm.createDialogWithData(this.getServerPrefix()+this.resourcetype,row,{'title':res.title,'hide_parameters':this.getAllHiddenParameters(),'show_description':this.show_description}).then(resourceData => {
                    resource.action('update', {'table':this.table,'rowid':rowid, 'data':resourceData}).then( result => {
                        this.scheduleReloadData();
                    }) 
                })
            })
        }
    }
    getServerPrefix() {
        if (this.main_server) {
            return 'server:'
        } else {
            return '';
        }
    }
    showAddDialog() {
        if (this.add_link) {
            let addLink =  this.add_link.replace(/\[table\]/g, this.table)
            router$.goto(addLink)
        } else {
            DialogForm.createDialogWithData(this.getServerPrefix()+this.resourcetype, {}, {'show_description':this.show_description,'hide_parameters':this.getAllHiddenParameters()}).then( resourceData => {
                resource.action('insert', {'table':this.table, 'resourceData':resourceData, 'where':this.where}).then(result => {
                    this.scheduleReloadData()
                })
            })
        }
    }
}
