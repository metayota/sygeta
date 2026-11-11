class Translator extends Tag {
    setup() {
        this.page = 0
        this.search = ''
        resource.action('get_languages').then(languages=> {
            this.setAttribute('languages', languages.map(v => {
                return {name:v.language_name,value:v.language_key}
            }))
        })
    }
    addNewTranslation() {
        DialogForm.createDialogWithData('add-translation',{language:translate('lang_key')}).then(newTranslation=> {
            resource.action('save',[newTranslation]).then(result=> {
                toast$.show(result.message,false,result.type)
            })
        })
    }
    autotranslate(translation) {
        
    }
    init() {
        let hash = window.location.hash
        if (hash != '' && hash != undefined) {
            this.setAttribute('search',hash.substring(1))
            history.pushState("", document.title, window.location.pathname + window.location.search); // remove the #
        }
        this.updateLanguageA(translate('language_code'))
    }
    updateLanguageA(lang) {
        this.language_a = lang
        this.reload()
    }
    updateLanguageB(lang) {
        this.language_b = lang
        this.reload()
    }
    resetTranslation(translation) {
        resource.action('reset_others',translation).then(result=> {
            translation.original_changed = false
            this.update('translation.original_changed')
        })
    }
    pressedEnter(translation) {
        let text = translation.translation != undefined ? translation.translation : ''
        translation.translation = text + "\n"
        this.update('this.translations')
    }
    pressedEnterB(translation) {
        let text = translation.translation_b != undefined ? translation.translation_b : ''
        translation.translation_b = text + "\n"
        this.update('this.translations')
    }
    reload() {
        resource.call({'language_a':this.language_a,'language_b':this.language_b,'page':this.page,'search':this.search}).then(result => {
            this.setAttribute('translations',result)
        })
        this.reloadCount()
    }
    updateSearch(search) {
        this.setAttribute('search',search)
        this.reload()
    }
    changedTranslation(translation,value) {
        translation.changed = true
        translation.original_changed = true
        translation.translation = value
        this.show_autotranslate = true
        this.update('this.show_autotranslate')
        this.update('translation.original_changed')

    }
    changedTranslationB(translation,value) {
        translation.changed = true
        translation.translation_b = value
        translation.language_b = this.language_b
    }
    reloadCount() {
        resource.call({'language_a':this.language_a,'language_b':this.language_b,'page':this.page,'search':this.search,'count':true}).then(result => {
            this.setAttribute('page',1)
            this.setAttribute('number_of_pages',Math.ceil(result.count / 15))
        })
    }
    saveChanges() {
        resource.action('save',this.translations.filter(t => t.changed == true))
    }
    pageChanged(page) {
        this.setAttribute('page',page)
        this.reload()
    }
    displayTextarea(translation) {
        if (translation.translation == undefined) {
            return false;
        }
        if (translation.translation.length > 150 || (translation.translation_b != undefined && translation.translation_b.length > 150)) {
            return true;
        }
        if (translation.translation.includes("\n")) {
            return true;
        }
        return false;
    }
}