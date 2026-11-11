class Dropdown extends RCBaseFormElement {
    setup() {
        this.default = true
        this.itemtag = 'rc.dropdown.text'
        this.sort = true
    }
    init() {
        this.searchOptionText = '';
        if (!this.chosenItems)
            this.chosenItems = []
        if (!this.stdtext)
            this.stdtext = translate('please_select')
    }
    setValues(values) {
        if (values.options !== undefined) {
            this._options = values.options;
        }
        if (values.value != undefined) {
            this._value = values.value;
        }
    }
    set stdtext(s) {
        this._stdtext = s
        this.update('this.getChosenItems')
    }
    get stdtext() {
        return this._stdtext
    }
    set name(n) {
        this._name = n
        this.update('this.name')
    }
    get name() {
        return this._name
    }
    set class(value) {

        this._class = value;
        this.update('this.class');
    }
    get class() {
        return this._class;
    }
    set itemtag(it) {
        this._itemtag = it
        this.update('this.itemtag')
    }
    get itemtag() {
        return this._itemtag
    }
    updateOptionSelections() {
        if (this.chosenItems) {
            for (let selectedOption of this.chosenItems) {
                let optionEl = this.getOptionElement(selectedOption)
                if (optionEl) {
                    optionEl.classList.add('selected');
                }
            }
        }
        // this.update('this.options')
    }
    set value(value) {
        if (value === null || value === undefined) {
            this._value = null;

            this.chosenItems = []
            this.update('tag.getChosenItems')
            this.update('tag.chosenItems')
            return;
        }

        //this._value = value;
        if (!this.options && !this.tempValue) {
            this.tempValue = value;
            return;
        } if (!this.options) return;
        this.tempValue = undefined;
        if (this.multiple || Array.isArray(value)) {
            this.chosenItems = [];
            for (let valueItem of value) {
                let foundItem = this.options.findIndex(option => option.value == valueItem);
                if (foundItem != -1) {
                    this.chosenItems.push(this.options[foundItem]);//
                }
            }
            this._value = this.chosenItems.map((option) => option?.value);
            this.updateOptionSelections()
        } else {
            let foundItem = this.options.findIndex(option => option?.value == value);
            if (foundItem == -1) {
                return;
            }
            this.chosenItems = [this.options[foundItem]]
            let foundItems = this.chosenItems.map((option) => option && option.value);
            if (foundItems)
                this._value = foundItems[0];
        }
        this.update('this.getChosenItems')
        this.update('this.chosenItems')
        this.updateSelectedItems()
    }
    get value() {
        return this._value;
    }
    set options(options) {
        if (typeof options ==  'string') {
            options = JSON.parse(options)
        }

        if (options && this.translate_options) {
            options = options.map(option => Object.assign({}, option, { name: translate(option.name) }))
            let translatedOptions = Object.assign({}, options)

        }
        this._sorted = false
        this._options = options;
        if (this.tempValue !== undefined) {
            this.value = this.tempValue;
        } else {
            this.value = this._value
        }
        this.update('this.options')
        this.updateOptionSelections()
        this.updateSelectedItems()
    }
    get options() {
        this.sortOptions()
        if (this.default && this._options) {
            return [{ name: this.stdtext, value: null, isPlaceholder: true }, ...this._options]
        }
        return this._options;
    }

    set errorMessage(em) {
        this._errorMessage = em
        this.update('this.errorMessage')
    }

    get errorMessage() {
        return this._errorMessage
    }
    
    set datasource(ds) {
        if (this._datasource != ds) {
            this._datasource = ds
            GlobalResource.call(ds).then(result => {
                this.options = result
            })
        }

    }
    get datasource() {
        return this._datasource
    }
    getChildIdx(option) {
        return this.options.findIndex((o) => o == option)
    }
    getOptionElement(option) {
        let childIdx = this.getChildIdx(option);
        if (this.itemlist) {
            var children = this.itemlist.children
            return children[childIdx];
        }
    }
    updateSelectedItems() {
        if (this.multiple) {
            for (let selectedOption of this.chosenItems) {
                this.getOptionElement(selectedOption)?.classList.add('selected');
            }
        }
    }
    isSelected(opt) {
        return this.chosenItems.some((selOption) => selOption.value === opt.value)
    }
    render(browserElement) {
        this.updateSelectedItems()
        return browserElement;
    }
    chooseOption(option) {
        this.focusOption(option, false)
        if (this.multiple) {
            if (!option.isPlaceholder) {
                let alreadySelectedItem = this.chosenItems.some((selOption) => selOption.value === option.value);
                if (alreadySelectedItem) {
                    this.getOptionElement(option).classList.remove('selected');
                    this.chosenItems = this.chosenItems.filter((selOption) => selOption.value !== option.value);
                } else {
                    this.chosenItems.push(option);
                    var childEl = this.getOptionElement(option).classList.add('selected');
                }
                this._value = this.chosenItems.map((option) => option.value);
            }
        } else {
            if (option.isPlaceholder) {
                this.value = null;
                this.chosenItems = []
            } else {
                this._value = option.value;
                this.chosenItems = [option];
            }
            this.hideOptions();
            this.focusOption(null)
        }
        this.unsetSearch()
        this.touched();
        this.update('tag.getChosenItems')
        this.update('tag.chosenItems')
        this.fire('change', this.value)
    }
    elementKeydown(event) {
        if (this.readonly) {
            return;
        }
        if (event.metaKey || event.ctrlKey) {
            return;
        }
        if (event.code == "Enter") {
            if (!this.itemListVisible) {
                this.showOptions();
            } else if (!this.chooseFocusedOption()) {
                this.toggleOptions();
            }
            event.preventDefault();
            event.stopPropagation();
        }
        this.handleNavKeys(event);
        if (event.key.length === 1 &&
            (event.key != ' ' || this.searchOptionText.length > 0)) {
            this.performSearch(event);
        }
        if (event.code == "Space") {
            if (this.searchTimer == null) {
                this.toggleOptions();
            }
            this.avoidScrollEvent(event);
        }
        if (event.code == "Escape") {
            this.hideOptions();
        }
        if (event.code == "Tab") {
            if (!this.multiple || !this.chosenItems || this.chosenItems.length == 0) {
                this.chooseFocusedOption();
            }
            this.hideOptions();
        }
    }
    set sort(s) {
        this._sort = s
        this.sortOptions()
    }
    get sort() {
        return this._sort
    }
    sortOptions() {
        if (this.sort && this._options && !this._sorted) {
            this._options.sort((a, b) => a.name?.localeCompare(b.name));
            this._sorted = true
        }
    }
    removeFocusedElement() {
        if (this.focusedElement) {
            this.focusedElement.classList.remove('focus')
            this.focusedElement.innerHTML = this.focusedOption.name
            this.focusedElement = null;
        }
    }
    focusOption(option, scrollToTop = true) {
        this.removeFocusedElement();
        if (this.itemlist != null && option != null) {
            var childEl = this.getOptionElement(option);
            if (!childEl) {
                return
            }
            if (scrollToTop) {
                this.itemlist.scrollTop = childEl.offsetTop
            } else {
                this.scrollElementVisible(childEl)
            }
            childEl.classList.add('focus');
            if (this.multiple && this.multipleEl) {
                this.multipleEl.nativeElement.focus();
            }
            this.focusedElement = childEl

            let idx = option.name.toLowerCase().indexOf(this.searchOptionText)
            let boldStr = '';
            boldStr += option.name.substr(0, idx);
            boldStr += '<b>'
            boldStr += option.name.substr(idx, this.searchOptionText.length)
            boldStr += '</b>'
            boldStr += option.name.substr(idx + this.searchOptionText.length)
            this.focusedElement.innerHTML = boldStr
        }
        this.focusedOption = option;
    }
    touched() {
        this._touched = true;
    }
    getOptionFromTextSearch() {
        var foundItem = null;
        if (this.searchOptionText.length > 0) {
            if (this.multiple) {
                this.showOptions();
            }
            this.options.forEach((option) => {
                if (foundItem == null) {
                    if (option.name.toLowerCase().startsWith(this.searchOptionText) && !option.isPlaceholder) {
                        foundItem = option;
                    }
                }
            });
            if (foundItem == null) {
                this.options.forEach((option) => {
                    if (foundItem == null) {
                        if (option.name.toLowerCase().includes(this.searchOptionText) && !option.isPlaceholder) {
                            foundItem = option;
                        }
                    }
                });
            }
        }
        return foundItem;
    }
    unsetSearch() {
        this.cancelTimer()
        this.searchOptionText = ''
        this.searchTimer = undefined

        if (this.focusedElement && this.focusedOption)
            this.focusedElement.innerHTML = this.focusedOption.name
    }
    cancelTimer() {
        if (this.searchTimer !== undefined) {
            window.clearTimeout(this.searchTimer)
            this.searchTimer = undefined
        }
    }
    hideOptions() {
        this.itemListVisible = false;
        this.update('tag.itemListVisible')
    }
    showOptions() {
        if (!this.itemListVisible) {
            this.itemListVisible = true;
            this.update('tag.itemListVisible');
            this.removeFocusedElement();
            if (this.chosenItems[0])
                this.focusOption(this.chosenItems[0]);
            else
                this.itemlist.scrollTop = 0;
        }
    }
    performSearch(event) {
        this.searchOptionText += event.key.toLowerCase();
        this.cancelTimer();
        let foundItem = null;
        while (this.searchOptionText.length > 0 && foundItem === null) {
            foundItem = this.getOptionFromTextSearch();
            if (foundItem === null) {
                this.searchOptionText = this.searchOptionText.substr(1)
            }
        }
        if (foundItem !== null) {
            if (!this.itemListVisible) {

                this.chosenItems = [foundItem];

                this.value = foundItem.value;
                this.touched();
                this.update('tag.getChosenItems')
                this.update('tag.chosenItems')
                this.fire('change', this.value)
            } else {
                this.searchTimer = window.setTimeout(function () {
                    this.unsetSearch();
                    if (!this.multiple && (this.chosenItems.length == 1) && !this.itemListVisible) {
                        this.chooseOption(this.chosenItems[0]);
                    }
                }.bind(this), 2000);
            }
        }
        this.focusOption(foundItem);
    }
    getChosenItems() {
        if (this.displaytext) {
            return this.displaytext
        }
        if (this.multiple) {
            return this.chosenItems && this.chosenItems.length > 0 ? this.chosenItems.map((option) => option?.name).join(', ') : this.stdtext;
        } else {
            return this.chosenItems && this.chosenItems.length > 0 ? (this.chosenItems[0].name) : this.stdtext;
        }
    }
    chooseFocusedOption() {
        if (this.itemlist != null) {
            if (this.focusedOption) {
                this.chooseOption(this.focusedOption)
                return true
            }
        }
        return false;
    }
    handleBlur(event) {
        this.touched();
        this.hasfocus = false
        window.setTimeout(function () {
            if (!this.hasfocus) {
                this.hideOptions();
            }
        }.bind(this), 300)
    }
    handleFocus(event) {
        this.hasfocus = true;
        this.update('tag.hasfocus')
    }
    avoidScrollEvent(event) {
        event.preventDefault();
        event.stopPropagation();
    }
    toggleOptions() {
        if (this.itemListVisible !== true) {
            this.showOptions()
            this.dropdownform.focus()
        } else {
            this.hideOptions();
        }
    }
    focus() {
        this.dropdownform.focus()
    }
    scrollElementVisible(element) {
        if (this.itemlist.scrollTop > element.offsetTop) {
            this.itemlist.scrollTop = element.offsetTop;
        }
        let selectedElementVisibleTop = element.offsetTop - this.itemlist.offsetHeight + element.offsetHeight;
        if (this.itemlist.scrollTop < selectedElementVisibleTop) {
            this.itemlist.scrollTop = selectedElementVisibleTop;
        }
    }
    handleNavKeys(event) {
        if (event.code == "ArrowUp" || event.code == "ArrowDown") {
            this.unsetSearch();
            if (this.itemlist != null) {
                var children = this.itemlist.children;
                let selectedChildIdx = -1;
                for (var i = 0; i < children.length; i++) {
                    var childEl = children[i];
                    if (childEl.classList.contains('focus')) {
                        selectedChildIdx = i;
                        childEl.classList.remove('focus')
                    }
                }
                for (var i = 0; i < children.length; i++) {
                    var childEl = children[i];
                    if (((event.code == "ArrowDown") && ((selectedChildIdx + 1) == i)) || ((event.code == "ArrowUp") && ((selectedChildIdx - 1) == i))) {
                        //if (i != children.length-1) {
                        this.focusOption(this.options[i], false)
                        //}
                    }
                }
                if ((selectedChildIdx == children.length - 1) && (event.code == "ArrowDown")) {
                    this.focusOption(this.options[children.length - 1], false);
                }
                var upperIdx = this.default ? 1 : 0;
                if ((selectedChildIdx <= upperIdx) && (event.code == "ArrowUp") || (this.default == true && selectedChildIdx == 0) || selectedChildIdx == -1) {
                    this.focusOption(this.options[this.default ? 1 : 0], false);
                }
                this.avoidScrollEvent(event);
            }
        }
        this.update('tag.searchOptionText')
    }
    clickedOption(option) {
        this.unsetSearch();
        this.chooseOption(option)
        this.dropdownform.focus();
        this.hasfocus = true;
        if (option.fn) {
            option.fn(option)
        }
    }
    handleMousedown(event) {
        this.dropdownform.focus();
        this.hasfocus = true;
        event.stopPropagation();
        event.preventDefault();
    }
}