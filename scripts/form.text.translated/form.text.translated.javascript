/***
 * 1) A new translation & Current language is not main language & autotranslate is not available THEN MAIN LANGUAGE IS REQUIRED...
 */
class FormTextTranslated extends RCBaseFormElement {
    setup() {
        this.not_found = true
        this._original_value = ''
        this.language = FormTextTranslated.current_language
    }

    init() {
        this.useTextarea = false
        this.disableBlur = true
        this.show_key = true
        this.main_language = resource.getData().main_language
        this.current_language = resource.getData().current_language
        this.auto_translate = resource.getData().auto_translate
        if (!this.main_language) {
            this.main_language = 'en'
            console.warn('Metayota: main_language is not set in config')
        }
    }

    set translation_category(tc) {
        this._translation_category = tc
    }
    get translation_category() {
        return this._translation_category
    }

    set use_textarea(ta) {
        this._use_textarea = ta
    }

    get use_textarea() {
        return this._use_textarea
    }

    set translated_value(tv) {
        this._translated_value = tv
        this.update('this.translated_value')
    }

    get translated_value() {
        return this._translated_value
    }

    setTranslation(translation) {
        this.setAttribute('recommended', [])
        this.value = translation.translation_key
        this.translated_value = translation.translation
        this.translationKeyChanged(translation.translation_key)
        this.updateUseTextarea();
    }

    toggleTranslationInfo() {
        if (this.translation_info.style.display == 'block') {
            this.translation_info.style.display = 'none'
        } else {
            this.translation_info.style.display = 'block'
        }
    }

    otherLanguageChanged(other_language, newValue) {
        other_language.translation = newValue
        if (other_language.language == this.main_language && !this.unique) {
            this.updateValueNonUnique()
        }
        this.generateRandomValue()
        this.fire('change', this._value) //doesn't really needed to be called all the time, just when it's really changed
        this.hasUnsavedChanges = true
    }

    translationKeyChanged(new_key) {
        this._value = new_key
        this._original_value = new_key
        this.fire('change', this._value)
        this.hasUnsavedChanges = true
        if (!this.create_new_translation) {
            this.loadTranslation(new_key)
        }
    }

    set label(l) {
        this._label = l
        this.update('this.label')
    }

    get label() {
        return this._label
    }

    set readonly(re) {
        this._readonly = re
    }

    get readonly() {
        return this._readonly
    }

    hideRecommended() {
        this.setAttribute('recommended', [])
    }

    loadRecommended(value) {
        if (!this.unique && this.translation_category) {
            resource.action('get_recommended', { query: value, area: this.translation_category }).then(result => {
                this.setAttribute('recommended', result)
                let existingOptions = result.map(item => {
                    return {
                        value: item.translation_key,
                        name: item.translation
                    };
                });
                existingOptions.push({
                    value: 'new_translation',
                    name: translate('new_translation') // Hier verwenden wir eine translate-Funktion
                });
                this.setAttribute('existing_options', existingOptions)
            })
        }
    }

    convertToTranslationKey(text) {
        let nameValue = text
        if (nameValue == undefined) {
            nameValue = Array.from({ length: 8 }, () => Math.random().toString(36).charAt(2)).join('');
        } else {
            nameValue = nameValue.replaceAll('-', '_').replaceAll(' ', '_').replaceAll('.', '_').toLowerCase()
        }
        return nameValue
    }

    // The value of the name field in the same form
    getNameValue() {
        return this.input ? this.input.form?.name?.value : this.textarea.form?.name?.value
    }

    generateRandomValue() {
        //if (this.unique && (this.not_found && (this._value == undefined || this._value == '')) && (this._original_value == '' || this._original_value == '')) {
        if (this.unique && this.not_found && this._original_value == '') {
            let nameRawValue = this.getNameValue()
            let nameValue = this.convertToTranslationKey(nameRawValue)
            this._value = (this.translation_category ? this.convertToTranslationKey(this.translation_category) + '_' : '') + this.convertToTranslationKey(this.name) + '_' + nameValue
            //this._original_value = this._value
            this.fire('change', this._value)
            this.update('this.value')
        }
    }

    textToTranslationKey(text) {
        return text.trim().replace(/(\d+)([a-zA-Z]+)/g, '$1_$2').replace(/([a-zA-Z]+)(\d+)/g, '$1_$2').replaceAll(' ', '_').replaceAll('-', '_').replaceAll('(', '_').replaceAll(')', '_').replaceAll('__', '_').replace(/[^a-z0-9]+/gi, "_").replace(/^_+|_+$/g, '').toLowerCase()
    }

    updateValueNonUnique() {
        if (this.not_found) {
            let mainTranslation = this.getMainTranslation()
            if (mainTranslation != undefined && mainTranslation != '') {
                this._value = this.textToTranslationKey(mainTranslation)
                this._value = this.translation_category ? this.textToTranslationKey(this.translation_category) + '_' + this._value : this._value
                if (this._value.length > 60) {
                    let category = this.translation_category ? this.textToTranslationKey(this.translation_category) + '_' : ''
                    let translation_key = category + this.name + '_' + Array.from({ length: 8 }, () => Math.random().toString(36).charAt(2)).join('');
                    this._value = translation_key
                }
            } else if (this._original_value != undefined) {
                this._value = this._original_value
            }
            this.update('this.value')
            this.fire('change', this._value)
        }
    }

    setExistingTranslation(value) {
        if (value == 'new_translation') {
            this.setAttribute('create_new_translation', true);
            if (!this.auto_translate && this.current_language != this.main_language) {
                let category = this.translation_category ? this.textToTranslationKey(this.translation_category) + '_' : ''
                let translation_key = category + Array.from({ length: 8 }, () => Math.random().toString(36).charAt(2)).join('');
                this.value = translation_key
            } else {
                this.value = this.translation_category
            }
            this.loadOtherLanguages('', false)
        } else {
            this.setAttribute('create_new_translation', false);
            this.value = value
            this.loadOtherLanguages(this._value, true)
        }
    }

    resourceChanged(value) {
        if (this.readonly === true) return

        this._translated_value = value
        if (value == '') {
            if (!this.unique) {
                this._value = ''
                this._original_value = ''
            }
        } else {
            if (!this.unique) {
                this.updateValueNonUnique()
            }
        }
        this.update('this.value')
        this.generateRandomValue()
        this.fire('change', this._value)
        this.updateUseTextarea()
        this.setAttribute('show_autotranslate', true)
        this.hasUnsavedChanges = true
    }

    keypress(e) {
        if (!e) e = window.event;
        var keyCode = e.keyCode || e.which;
        if (keyCode == '13') {
            //this.fire('enter', 'pressed enter')
            if (!this.getUseTextarea()) {
                if (this.translated_value == undefined) {
                    this.translated_value = "\n"
                } else {
                    this.translated_value += "\n"
                }
            }
            this.updateUseTextarea()
            return false;
        }
    }

    blur() {
        window.setTimeout(() => {
            this.hideRecommended()
        }, 800)

        if (!this.disableBlur && !this.unique) {
            resource.action('get_translation', { translation_key: this.value }).then(result => {
                if (result) {
                    if (!result.not_found) {
                        this._translated_value = result.translation
                    }
                    this.updateUseTextarea();
                    this.setAttribute('editable', false)
                } else {
                    this.setAttribute('editable', true)
                }
            });
        }
    }

    saveTranslations() {
        if (this.value != undefined && this.value != '') {
            Translation.translations[this.value] = this._translated_value

            let translations = [];

            // Add the main translation
            let mainTranslation = { //         'translation_key': this.value,
                'translation': this._translated_value,
                'language': FormTextTranslated.current_language
            };
            if (!this.unique) {
                mainTranslation.translation_category = this.translation_category;
            }
            translations.push(mainTranslation);

            // Add other language translations
            this.other_languages.forEach(other_language => { //     other_language.translation_key = this.value;
                translations.push(other_language);
            });

            // Send all translations in one request
            resource.action('save_translations', { translation_key:this.value, translations: translations });

            this.fire('saved', this.value);
            this.setAttribute('show_autotranslate', false);
        }
    }

    isValid() {
        if (!super.isValid()) {
            return false
        }
        if (this.create_new_translation && !this.getMainTranslation()) { //&& !this.auto_translate
            this.setAttribute('errorMessage', translate('fill_out_main_translation', this.getMainLanguageTranslated()))
            return false
        }
        //this.saveTranslations()
        
        let promise = this.checkAutoTranslate(!this.wait_for_translations, true, false)
        if (this.wait_for_translations && (promise instanceof Promise)) {
            return promise
        }
        return true
    }

    loadOtherLanguages(key, including_main_language) {
        resource.action('get_translation', { translation_key: key, including_main_language: including_main_language }).then(result => {

            if (result != undefined && result.translation !== undefined) {
                this.setAttribute('other_languages', result.other_languages)
                this.update('this.getOtherTranslationNotEmpty')
            }
        })
    }

    getAllTranslations() {
        let allTranslations = []
        if (!this.other_languages) {
            this.other_languages = []
            for(let language of FormTextTranslated.languages) {
                this.other_languages.push({
                    language_translated:language.language_translated,
                    language: language.language_key,
                    translation_key: this.value,
                    translation: ''
                })
            }
        }
        
            allTranslations.push(...this.other_languages)
        
        let translation = { 'translation': this._translated_value, 'language': this.language }
        allTranslations.push(translation) // current language
        return allTranslations
    }

    getMainLanguageTranslated() {
        let mainLanguage = this.other_languages.find(t => t.language == this.main_language)
        return mainLanguage.language_translated
    }

    getMainTranslation() {
        let mainTranslation = this.getAllTranslations().find(t => t.language == this.main_language)
        if (mainTranslation != undefined) {
            return mainTranslation.translation
        }
        return undefined
    }

    getFilledTranslations(allTranslations) {
        // Destructure main_language and current_language from this
        const { main_language, current_language } = this;

        // Filter for non-empty translations directly in the filter function
        let filledTranslations = allTranslations.filter(item =>
            item.translation !== undefined &&
            item.translation !== null &&
            item.translation.trim() !== ''
        );

        // Check if there is a translation for the current language
        /*   let currentLangTranslations = filledTranslations.filter(item => item.language === current_language);
           if (currentLangTranslations.length > 0) {
               return currentLangTranslations;
           }
   
           // Check if there is a translation for the main language
           let mainLangTranslations = filledTranslations.filter(item => item.language === main_language);
           if (mainLangTranslations.length > 0) {
               return mainLangTranslations;
           }*/

        // If neither current nor main language has translations, return all filled translations
        return filledTranslations;
    }


    checkAutoTranslate(closeConnection = false, save = false, replaceAllTranslations = true) {
        let allTranslations = this.getAllTranslations()
        let emptyTranslations;//.filter(item => !item.translation);
        let filledTranslations;
        let emptyLanguages;
        if (replaceAllTranslations) {
            emptyTranslations = allTranslations.filter(item => item.language != this.current_language);//.filter(item => !item.translation);
            filledTranslations = this.getFilledTranslations(allTranslations).filter(item => item.language == this.current_language);
            emptyLanguages = emptyTranslations.map(item => item.language);
        } else {
            emptyTranslations = allTranslations.filter(item => !item.translation);
            filledTranslations = this.getFilledTranslations(allTranslations);
            emptyLanguages = emptyTranslations.map(item => item.language);
        }

        if (this.value && (emptyLanguages.length > 0 || this.hasUnsavedChanges || save)) {
            if (this.hasUnsavedChanges && save && closeConnection) {
                this.hasUnsavedChanges = false
            }
            this.setAttribute('translationInProgress', true)
            return resource.action('autotranslate', { 'close_connection': closeConnection, 'save': save, 'translation_category': this.translation_category, 'row_name': this.getNameValue(), 'field_name': this.name, 'translation_key': this.value, 'existing_translations': filledTranslations, 'needed_languages': emptyLanguages }).then(result => {
                this.setAttribute('translationInProgress', false)
                if (this.hasUnsavedChanges && save && !this.closeConnection) {
                    this.hasUnsavedChanges = false
                }
                for (let key in result) {
                    let value = result[key];
                    if (key == this.language) {
                        if (this.input) {
                            this.input.value = value
                        } else if (this.textarea) {
                            this.textarea.value = value
                        }
                        this.translated_value = value
                    } else {
                        let langObj = this.other_languages.find(item => item.language == key);
                        if (langObj) {
                            langObj.translation = value;
                        }
                    }
                }
                this.update('this.other_languages')
                this.update('this.getOtherTranslationNotEmpty')
                //  this.saveTranslations()
                this.setAttribute('show_autotranslate', false)
            })
        }
    }

    getOtherTranslationNotEmpty() {
        // If the property doesn't exist or isn't an array, return null
        if (!this.other_languages || !Array.isArray(this.other_languages)) {
            return null;
        }

        // Find the first language object where the translation is not an empty string
        const firstValidLanguage = this.other_languages.find(lang => lang.translation && lang.translation.trim() !== '');

        // If found, return the translation, otherwise return null
        return firstValidLanguage ? firstValidLanguage.translation : null;
    };


    loadTranslation(key) {
        resource.action('get_translation', { translation_key: key }).then(result => {
            if (result != undefined) {

                //if (!result.not_found) {
                this.not_found = result.not_found
                this.language = result.language
                if (result.translation != undefined) {
                    if (this.input != undefined) {
                        this.input.value = result.translation
                    }
                    this.translated_value = result.translation
                    this.updateUseTextarea();
                }
                this.setAttribute('other_languages', result.other_languages)
                this.update('this.getOtherTranslationNotEmpty')
                const hasMissingTranslation = result.other_languages.some(
                  lang => lang.translation === undefined || lang.translation === null || lang.translation === ''
                );
                debugger
                if (result.translation && hasMissingTranslation) {
                    this.setAttribute('show_autotranslate', true)
                }
                //}
            }
        })
    }

    getUseTextarea() {
        if (this.use_textarea !== undefined) {
            return this.use_textarea
        }
        if (!this.unique) {
            return false
        }
        if (this._translated_value != undefined) {
            if (this._translated_value.length > 200) {
                return true;
            }
            if (this._translated_value.includes("\n")) {
                return true
            }
        }
        return false
    }

    updateUseTextarea() {
        this.disableBlur = true

        let giveFocus = document.activeElement != undefined && (this.input == document.activeElement || this.textarea == document.activeElement);
        this.update('this.getUseTextarea')
        if (giveFocus) {
            if (this.getUseTextarea()) {
                this.textarea.focus()
            } else {
                this.input.focus()
            }
        }
        this.update('this.translated_value');
        this.disableBlur = false
    }

    set value(v) {
        if (v === undefined || v === null) {
            v = ''
        }
        // Check if v is an object with the simple structure {value: translation_key, translations: {...}}
        if (typeof v === 'object' && v.value !== undefined && v.translations !== undefined) {
            // Set the translation key
            this._value = v.value
            this._original_value = v.value
            this.update('this.value')

            // Get current language translation
            if (v.translations[this.current_language]) {
                this.translated_value = v.translations[this.current_language]
                if (this.input != undefined) {
                    this.input.value = v.translations[this.current_language]
                }
                if (this.textarea != undefined) {
                    this.textarea.value = v.translations[this.current_language]
                }
            }

            // Build other_languages array for the component
            const otherLanguages = []
            for (const [lang, translation] of Object.entries(v.translations)) {
                if (lang !== this.current_language) {
                    otherLanguages.push({
                        language: lang,
                        language_translated: translate('language_' + lang),
                        translation: translation,
                        translation_key: this._value
                    })
                }
            }

            this.language = this.current_language
            this.not_found = false
            this.setAttribute('other_languages', otherLanguages)
            this.updateUseTextarea()
            this.update('this.getOtherTranslationNotEmpty')
            this.loadRecommended()
        } else if (typeof v === 'string') {
            // Handle traditional string format (translation_key)
            this._value = v
            this._original_value = v
            this.update('this.value')
            this.loadTranslation(v)
            this.loadRecommended()
        }
    }
    get value() {
        return this._value
    }

    set type(t) {
        this._type = t
        this.update('this.type')
    }

    get type() {
        return this._type
    }
    set placeholder(p) {
        this._placeholder = p
        this.update('this.placeholder')
    }

    get placeholder() {
        return this._placeholder
    }
}