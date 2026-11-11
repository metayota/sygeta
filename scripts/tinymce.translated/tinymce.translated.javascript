class TinyMCETranslatedTag extends RCBaseFormElement {
    init() {
        this.main_language = resource.getData().main_language
        this.current_language = resource.getData().current_language
        this.all_languages = resource.getData().all_languages
        if (!this.main_language) {
            this.main_language = 'en'
            console.warn('Metayota: main_language is not set in config')
        }
        this.not_found = false
        this.last_translated_content = '' // Track last content that was auto-translated
    }

    set translation_category(tc) {
        this._translation_category = tc
    }
    get translation_category() {
        return this._translation_category
    }

    set translated_value(tv) {
        this._translated_value = tv
        this.update('this.translated_value')
    }

    get translated_value() {
        return this._translated_value
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

    convertToTranslationKey(text) {
        if (!text) {
            return Array.from({ length: 8 }, () => Math.random().toString(36).charAt(2)).join('');
        }
        return text.replaceAll('-','_').replaceAll(' ','_').replaceAll('.','_').toLowerCase()
    }

    getNameValue() {
        // Start from formBoxEl and traverse up the DOM tree to find a form element
        let currentElement = this.formBoxEl;
        let formElement = null;
        
        while (currentElement && currentElement.parentElement) {
            currentElement = currentElement.parentElement;
            if (currentElement.tagName && currentElement.tagName.toLowerCase() === 'form') {
                formElement = currentElement;
                break;
            }
        }
        
        // If we found a form, look for a name field within it
        if (formElement) {
            const nameField = formElement.querySelector('[name="name"]');
            if (nameField) {
                return nameField.value;
            }
        }
        
        return undefined;
    }

    textToTranslationKey(text) {
        return text.trim().replace(/(\d+)([a-zA-Z]+)/g, '$1_$2').replace(/([a-zA-Z]+)(\d+)/g, '$1_$2').replaceAll(' ', '_').replaceAll('-', '_').replaceAll('(', '_').replaceAll(')', '_').replaceAll('__', '_').replace(/[^a-z0-9]+/gi, "_").replace(/^_+|_+$/g, '').toLowerCase()
    }

    generateTranslationKey() {
        // This should ONLY be called from onTinyMCEChange when translation key is empty
        let meaningfulPart = ''
        
        // Get meaningful part from context only (name field or component name)
        let nameValue = this.getNameValue()
        if (nameValue) {
            meaningfulPart = this.convertToTranslationKey(nameValue)
        } else if (this.name) {
            meaningfulPart = this.convertToTranslationKey(this.name)
        }
        
        // Add category prefix if available
        let category = this.translation_category ? this.textToTranslationKey(this.translation_category) + '_' : ''
        
        // Always add random 8 character string
        let randomString = Array.from({ length: 8 }, () => Math.random().toString(36).charAt(2)).join('');
        
        // Build the key from context only
        if (meaningfulPart) {
            this._value = category + meaningfulPart + '_' + randomString
        } else {
            this._value = category + this.name + '_' + randomString
        }
        
        this.not_found = false // We now have a translation key
        this.update('this.value')
    }

    onTinyMCEChange(content) {
        this._translated_value = content
        
        // ONLY set translation key manually when TinyMCE change event occurs AND this.value is empty
        if ((!this._value || this._value === '') && content && content.trim() !== '') {
            this.generateTranslationKey()
        }
        
        // THE TRANSLATION KEY STAYS THE SAME - even if text content changes
        this.fire('change', this._value)
        this.setAttribute('showAutotranslate',true)
    }

    isValid() {
        // DO NOT generate translation key here - it should already exist from onTinyMCEChange
        if (!this._value || this._value === '') {
            console.warn('Translation key is missing - should have been set in onTinyMCEChange')
            return false
        }
        
        // Call auto-translate once during validation
        let promise = this.checkAutoTranslate()
        if (this.wait_for_translation && (promise instanceof Promise)) {
            return promise
        }
        
        return true
    }

    loadTranslation(key) {
        if (!key || key === '') {
            return
        }
        
        
        GlobalResource.action('form.text.translated', 'get_translation', { 
            translation_key: key 
        }).then(result => {
            if (result != undefined) {
                this.not_found = result.not_found
                this.language = result.language
                
                if (result.translation != undefined) {
                    this.translated_value = result.translation
                    this.last_translated_content = result.translation // Set initial content
                }
                
                // Store other_languages but don't update UI since we only display current language
                this.other_languages = result.other_languages
            } else {
                this.not_found = true
            }
        })
    }

    checkAutoTranslate() {
        this.setAttribute('showAutotranslate', false)
        if (!this._translated_value) return
        
        // Only auto-translate if content has actually changed
        if (this._translated_value === this.last_translated_content) {
            return // Content hasn't changed, no need to auto-translate
        }

    
        // Initialize other_languages from all_languages if empty
        if (!this.other_languages || this.other_languages.length === 0) {
            if (this.all_languages && this.all_languages.length > 0) {
                this.other_languages = this.all_languages.map(lang => ({
                    language: lang.language_key,
                    translation: ""  // Set empty translation initially
                }))
            }
        }
        

        // Only include the current language translation - other languages will be generated
        let filledTranslations = [{
            'translation': this._translated_value, 
            'language': this.current_language
        }]

        // Get all languages that need translation (all languages except current)
        let emptyLanguages = []
        if (this.other_languages) {
            emptyLanguages = this.other_languages
                .filter(item => item.language != this.current_language)
                .map(item => item.language)
        }

       // if (emptyLanguages.length > 0) {
            this.setAttribute('translating', true)
            return GlobalResource.action('form.text.translated', 'autotranslate', {
                'translation_category': this.translation_category, 
                'row_name': this.getNameValue(),
                'field_name': this.name, 
                'translation_key': this._value, 
                'existing_translations': filledTranslations,  // Only current language
                'needed_languages': emptyLanguages,
                'save': true  // Use autotranslate's save flag
            }).then(result => {
                // Don't update other_languages in UI since we only display current language
                // Just store them for future reference
                this.setAttribute('translating', false)
                if (result && this.other_languages) {
                    for (let key in result) {
                        let value = result[key]
                        if (key != this.current_language) {
                            let langObj = this.other_languages.find(item => item.language == key)
                            if (langObj) {
                                langObj.translation = value
                            }
                        }
                    }
                }
                
                // Update last translated content to avoid re-translating the same content
                this.last_translated_content = this._translated_value
            })
       // }
    }

    set value(v) {
        if (v === undefined || v === null) {
            v = ''
        }
        this._value = v
        this.update('this.value')
        this.loadTranslation(v)
    }

    get value() {
        // Always return the current translation key - never auto-generate here
        return this._value
    }
}