class ViewFile extends Tag {
    isImage() {
        return this.value && (this.value.endsWith('jpg') || this.value.endsWith('png') || this.value.endsWith('webp') || this.value.endsWith('jpeg') || this.value.endsWith('svg') || this.value.endsWith('pdf'))
    }

    getPath() {
        if (this.value?.startsWith('/cache') || this.value?.startsWith('/uploads')) {
            return this.value
        }
        return '/uploads/'+this.folder+'/'+this.value;
    }

    getPathWithResize() {
        if (this.getPath().endsWith('.svg')) {
            return this.getPath()
        }
        return '/call/image.resize?w=200&format=webp&src='+this.getPath()
    }

    set value(v) {
        this._value = v
        this.update('this.getPathWithResize')
        this.update('this.value')
    }

    get value() {
        return this._value
    }
}