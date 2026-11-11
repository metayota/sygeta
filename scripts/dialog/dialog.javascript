class DialogForm extends Tag {

    init() {
        // Register a global keypress event listener
        this.keyPressHandler = this.keyPressed.bind(this); // Ensure this refers to the DialogForm instance
        document.addEventListener('keydown', this.keyPressHandler);
    }

    destroy() {
        // Remove the global keypress event listener
        document.removeEventListener('keydown', this.keyPressHandler);
    }

    keyPressed(e) {
        if (e.keyCode === 27) { // 27 is the key code for ESC
            this.hide(); // Assuming that `hide()` method is equivalent to `close()`
        }
    }

    keyPressed(e) {
        if (e.keyCode == 27) {
            this.hide()
        }
        //alert(e.which);
    }

    static displayTagInDialog(tag,data=null) {
        return Tag.ready(tag).then(ready=> {
            let dialog = Tag.render('dialog',{display_tag:tag,tagdata:data,visible:true});
            document.body.appendChild(dialog.node)
            
            return new Promise(function (resolve) {
                dialog.tag.resolve = resolve
            })
        })
	}

	static createDialogWithTag(tag,data=null) {
		let dialog = Tag.render('dialog',{tag:tag,tagdata:data,visible:true});
		document.body.appendChild(dialog.node)

        /*return new Promise(function (resolve) {
            dialog.tag.onsubmit = resolve
            //Object.assign(dialog.tag,data)
        })*/
        return Tag.ready(tag);
	}

    static createDialogWithData(resourcetype, data = {}, options={}) {
        let mainResolve = null;
        let promise = new Promise(function (resolve) {
            mainResolve = resolve
        })

        Tag.ready('dialog').then(result => {
            let dialog = Tag.render('dialog');
            dialog.tag.hide_parameters = options.hide_parameters
            dialog.tag.title = options.title
            dialog.tag.resourcetype = resourcetype
            dialog.tag.tagdata = data
            dialog.tag.onsubmit = mainResolve
            dialog.tag.show_description = options.show_description
            dialog.tag.resource_form_options = options
            document.body.appendChild(dialog.node)

            Resource.loaded(resourcetype).then( resource => {
                dialog.tag.visible = true
            })
        })


        return promise
    }

    static createDialog(resourcetype, parameters = {}) {
        let mainResolve = null
        let promise = new Promise(function (resolve) {
            mainResolve = resolve
        })

        Tag.ready('dialog').then( result => {
                let dialog = Tag.render('dialog');        
                dialog.tag.resourcetype = resourcetype
                //dialog.tag.visible = true
                dialog.tag.tagdata = parameters
                document.body.appendChild(dialog.node)
                
                Resource.loaded(resourcetype).then( resource => {
                    dialog.tag.visible = true
                })
                return new Promise(function (resolve) {
                    dialog.tag.onsubmit = mainResolve
                })

        })
        return promise
        
    }

    set tagdata(t) {
        this._tagdata = t 
        this.update('this.tagdata')
    }

    get tagdata() {
        return this._tagdata
    }

    submit(data) {
        if (this.form != undefined) {
            if (this.form.isValid()) {
                this.fire('submit',data)
            }
        } else {
            this.fire('submit',data)
        }
        this.hide()
    }

    fire(eventname, event) {
        super.fire(eventname, event)
        if (eventname == 'submit' && this.onsubmit) {
            this.onsubmit(event);
            this.hide()
        }
    }

    render() {
        //this.dialogModal.focus()
    }

    set resourcetype(rt) {
        this._resourcetype = rt
        this.update('this.resourcetype')
    }

    get resourcetype() {
        return this._resourcetype
    }

    show() {
        this.visible = true
        this.dialogModal.focus()
    }

    hide() {
        this.visible = false
        // TODO: Should be removed from DOM
    }

    set visible(v) {
        this._visible = v
        this.update('this.visible')
    }

    get visible() {
        return this._visible
    }

    set label(l) {
        this._label = l
        this.update('this.label')
    }

    get label() {
        return this._label
    }
}