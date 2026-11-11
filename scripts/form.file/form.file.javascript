class FormFile extends RCBaseFormElement {

	setup() {
		this.uploadLabel = translate('choose_file')
	}

	set uploading(u) {
		this._uploading = u
		this.update('this.uploading')
	}

	get uploading() {
		return this._uploading
	}

    set folder(f) {
        this._folder = f
        this.update('this.getPathWithResize')
    }


    get folder() {
        return this._folder
    }
    
    set value(v) {
        this._value = v
        this.update('this.getPathWithResize')
    }

    get value() {
        return this._value
    }

    getPath() {
       return '/uploads/'+this.folder+'/'+this.value
    }

    getPathWithResize() {
        if (this.value?.endsWith('.svg')) {
            return this.getPath()
        }
        return '/call/image.resize?w=200&format=webp&src='+this.getPath()
    }

    removeFile() {
        this.setAttribute('value','')
        this.fire('change','')
    }

    isImage() {
        return this.value && (this.value.endsWith('jpg') || this.value.endsWith('png') || this.value.endsWith('webp') || this.value.endsWith('jpeg')|| this.value.endsWith('svg'))
    }

    chooseFile() {
		Tag.ready('form.file.helper').then(ready => {
			let renderedTag = TagModel.renderTag('form.file.helper',{folder:this.folder})
			document.body.appendChild(renderedTag.node)
			renderedTag.tag.chooseFile()
			renderedTag.tag.fileTag = this

			renderedTag.tag.fileChosen = function(result) {
				this.setAttribute('value', result)
				this.fire('change',this.value)
			}.bind(this)

            renderedTag.tag.errorHappened = function(result) {
                this.setAttribute('errorMessage',result.message)
				this.setAttribute('value', '')
                this.setAttribute('uploading', false)
				this.fire('change',this.value)
			}.bind(this)
		})
    }
}