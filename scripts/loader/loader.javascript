class Loader extends Tag {
    set loading(l) {
        this._loading = l
        this.update('loading')
    }

    get loading() {
        return this._loading
    }
}