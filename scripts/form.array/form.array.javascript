class MetayotaFormArray extends RCBaseFormElement {
    setup() {
        this._value = []
    }
    set value(v) {
        if (typeof v == 'string' && v != '') {
            v = JSON.parse(v)
        }
        if (v) {
            this._value = v
        } else {
            this._value = []
        }
        this.update('this._value')
    }
    get value() {
        if (typeof this._value === 'object') {

            return JSON.stringify(this._value)
        } else {
            return this._value
        }
    }
    set resourcetype(r) {
        if (r) {
            this._resourcetype = r
            Tag.registerAndLoad(r)
            Resource.loaded(r).then( resource => {
                this.resource = resource
                this.update('this.resource')
                this.update('this._value')
            })
            this.update('this.resourcetype')
        }
    }
    editItem(i) {
        let item = this._value[i]
         Resource.cls('dialog').createDialogWithData(this.resourcetype, item).then( item => {
            this._value[i] = item
            this.update('this._value')
            this.changed()
        })
    }
    deleteItem(i) {
        this._value.splice(i,1)
        this.resourceUpdated(this.value)
    }
    get resourcetype() {
        return this._resourcetype
    }
    resourceUpdated(value,index) {
        this._value[index] = value;
        this.update('this._value')
        this.changed()
    }
    addItemDialog() {
        Resource.cls('dialog').createDialogWithData(this.resourcetype, {}).then( item => {
            this.addItem(item)
        })
    }
    changed() {
        this.fire('change',this.value)
    }
    addItem(value) {
        this._value.push(value);
        this.update('this._value')
        this.changed()
    }
    allowDrop(ev,node) {
        ev.preventDefault();
        node.classList.add('active')
    }
    leaveDroparea(event,node) {
        node.classList.remove('active')
    }

    drag(ev,index) {
        ev.dataTransfer.setData("index", index);
    }

    drop(ev, newIndex) {
        ev.preventDefault();
        var index = ev.dataTransfer.getData("index");

        this._value.splice(newIndex, 0, this.value.splice(index, 1)[0]);
        this.update('this._value')
        //ev.target.appendChild(document.getElementById(data));
    }
}