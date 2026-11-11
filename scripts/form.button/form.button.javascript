class MetayotaFormButton extends Tag {
    set title(t) {
        this.label = t
    }
    set label(l) {
        this._label = l
        this.update('this.label')
    }
    get label() {
        return this._label
    }
    set enabled(e) {
        this._enabled = e
        this.update('this.enabled')
    }
    get enabled() {
        if (this._enabled === undefined) {
            return true
        }
        return this._enabled
    }
    performClick() {
        if (this.enabled) {
            let promise = this.fire('click')
            if (promise) {
                this.setAttribute('in_progress',true)
                this.enabled = false
                promise.then(result=> {
                    this.enabled = true
                    this.setAttribute('in_progress',false)
                })
            }
        } 
    }
}