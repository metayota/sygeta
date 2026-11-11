class FormFileHelper extends Tag {

    setup() {
        this.iframeName = 'uploadiframe'+Math.random()*10000
        this.completed = true
    }

    chooseFile() {
        this.file.click()
    }

    render() {
        window.addEventListener('message',function(e) {
            if (e.data.iframe == this.iframeName) {
                if (e.data.message != undefined) {
                    toast$.show(e.data.message,true)
                    this.completed = false
			        this.fileTag.uploading = false
                } else {
                    this.uploadComplete(e.data.filename)
                }
            }
        }.bind(this))
    }

    fileSelected() {
        if (this.fileChosen) {
            //this.fileChosen()
            this.form.submit()
            this.completed = false
			this.fileTag.uploading = true
        }
    }

    uploadComplete(event) {
        this.fileChosen(event)
        this.filename = event
        this.completed = true
		this.fileTag.uploading = false
    }

    isValid() {
        if (!this.completed) {
            this.setAttribute('errorMessage', 'Please wait for the upload to be completed...')
        }
        return this.completed
    }

    destroy() {
        alert('gets destroyed')
        super.destroy()
    }
}