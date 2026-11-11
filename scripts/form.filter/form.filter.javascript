class DropdownFilter extends RCBaseFormElement {
    setup() {
        this.default = false;
        this.itemtag = 'rc.dropdown.text';
        this.sort = true;
        this.multiple = true;
    }

    init() {
        if (!this.chosenItems) {
            this.chosenItems = [];
        }
        if (!this.stdtext) {
            this.stdtext = translate('please_select');
        }
        this.selectedBubbleIndex = -1;
        this.searchText = '';
        this.foundOptions = [];
    }

    setValues(values) {
        if (values.options !== undefined) {
            this._options = values.options;
        }
        if (values.value != undefined) {
            this._value = values.value;
        }
    }

    // Getters and setters
    set stdtext(s) {
        this._stdtext = s;
        this.update('this.stdtext');
    }
    get stdtext() {
        return this._stdtext;
    }

    set name(n) {
        this._name = n;
        this.update('this.name');
    }
    get name() {
        return this._name;
    }

    set class(value) {
        this._class = value;
        this.update('this.class');
    }
    get class() {
        return this._class;
    }

    set itemtag(it) {
        this._itemtag = it;
        this.update('this.itemtag');
    }
    get itemtag() {
        return this._itemtag;
    }

    set multiple(m) {
        this._multiple = m;
        this.update('this.multiple');
    }
    get multiple() {
        return this._multiple !== false;
    }

    set value(value) {
        if (value === null || value === undefined) {
            this._value = this.multiple ? [] : null;
            this.chosenItems = [];
            this.update('this.chosenItems');
            return;
        }

        if (!this.options && !this.tempValue) {
            this.tempValue = value;
            return;
        }
        if (!this.options) return;
        
        this.tempValue = undefined;
        this.chosenItems = [];

        if (this.multiple && Array.isArray(value)) {
            for (let valueItem of value) {
                let foundItem = this.options.find(option => option.value == valueItem);
                if (foundItem) {
                    this.chosenItems.push(foundItem);
                }
            }
            this._value = this.chosenItems.map(option => option.value);
        } else if (!this.multiple) {
            let foundItem = this.options.find(option => option.value == value);
            if (foundItem) {
                this.chosenItems = [foundItem];
                this._value = foundItem.value;
            }
        }
        
        this.update('this.chosenItems');
    }

    get value() {
        if (this.multiple) {
            return this.chosenItems.map(item => item.value);
        } else {
            return this.chosenItems.length > 0 ? this.chosenItems[0].value : null;
        }
    }

    set options(options) {
        if (options && this.translate_options) {
            options = options.map(option => Object.assign({}, option, {name: translate(option.name)}));
        }
        this._sorted = false;
        this._options = options;
        
        if (this.tempValue !== undefined) {
            this.value = this.tempValue;
        } else {
            this.value = this._value;
        }
        this.update('this.options');
        this.performSearch();
    }

    get options() {
        this.sortOptions();
        return this._options || [];
    }

    set datasource(ds) {
        if (this._datasource != ds) {
            this._datasource = ds;
            GlobalResource.call(ds).then(result => {
                this.options = result;
            });
        }
    }
    get datasource() {
        return this._datasource;
    }

    set sort(s) {
        this._sort = s;
        this.sortOptions();
    }
    get sort() {
        return this._sort;
    }

    sortOptions() {
        if (this.sort && this._options && !this._sorted) {
            this._options.sort((a, b) => a.name?.localeCompare(b.name));
            this._sorted = true;
        }
    }

    // Bubble management
    selectBubble(index) {
        this.selectedBubbleIndex = index;
        this.update('this.selectedBubbleIndex');
    }

    removeBubble(index, event) {
        if (event) {
            event.stopPropagation();
            event.preventDefault();
        }
        
        this.chosenItems.splice(index, 1);
        if (this.selectedBubbleIndex >= index) {
            this.selectedBubbleIndex = Math.max(-1, this.selectedBubbleIndex - 1);
        }
        
        this.update('this.chosenItems');
        this.update('this.selectedBubbleIndex');
        this.fire('change', this.value);
        this.focusInput();
    }

    removeSelectedBubble() {
        if (this.selectedBubbleIndex >= 0 && this.selectedBubbleIndex < this.chosenItems.length) {
            this.removeBubble(this.selectedBubbleIndex);
        }
    }

    // Search and filtering
    getOptionsFromTextSearch() {
        let foundItems = [];
        let includesItems = [];
        
        // Safety check for searchInput element
        if (this.searchInput) {
            this.searchText = this.searchInput.textContent || '';
        } else {
            this.searchText = '';
        }
        
        let searchTxt = this.searchText.toLowerCase().trim();
        
        if (searchTxt.length === 0) {
            return this.options.filter(option => !this.isAlreadySelected(option));
        }

        this.options.forEach((option) => {
            if (!this.isAlreadySelected(option) && !option.isPlaceholder) {
                if (option.name.toLowerCase().startsWith(searchTxt)) {
                    foundItems.push(option);
                } else if (option.name.toLowerCase().includes(searchTxt)) {
                    includesItems.push(option);
                }
            }
        });

        return foundItems.concat(includesItems);
    }

    isAlreadySelected(option) {
        return this.chosenItems.some(item => item.value === option.value);
    }

    performSearch(event) {
        this.foundOptions = this.getOptionsFromTextSearch();
        
        if (this.foundOptions.length > 0 && this.searchText.trim()) {
            this.showOptions();
        } else if (this.searchText.trim() === '') {
            this.hideOptions();
        }
        
        // Auto-select first option when typing
        if (this.foundOptions.length > 0) {
            this.focusOption(this.foundOptions[0]);
        } else {
            this.focusOption(null);
        }
        
        this.update('this.foundOptions');
        this.update('this.searchText');
    }

    // Input handling
    handleInputKeydown(event) {
    const cursorPosition = this.getCursorPosition();
    
    if (event.code === "Backspace" && cursorPosition === 0) {
        event.preventDefault();
        if (this.selectedBubbleIndex >= 0) {
            this.removeSelectedBubble();
        } else if (this.chosenItems.length > 0) {
            this.selectedBubbleIndex = this.chosenItems.length - 1;
            this.update('this.selectedBubbleIndex');
        }
        return;
    }

    if (event.code === "ArrowLeft" && cursorPosition === 0) {
        event.preventDefault();
        if (this.selectedBubbleIndex === -1 && this.chosenItems.length > 0) {
            // Select the rightmost bubble when coming from input
            this.selectedBubbleIndex = this.chosenItems.length - 1;
        } else if (this.selectedBubbleIndex > 0) {
            // Move to the previous bubble
            this.selectedBubbleIndex = this.selectedBubbleIndex - 1;
        }
        this.update('this.selectedBubbleIndex');
        return;
    }

    if (event.code === "ArrowRight") {
        event.preventDefault();
        if (this.selectedBubbleIndex >= 0) {
            if (this.selectedBubbleIndex < this.chosenItems.length - 1) {
                // Move to the next bubble
                this.selectedBubbleIndex = this.selectedBubbleIndex + 1;
            } else {
                // Move back to input field
                this.selectedBubbleIndex = -1;
                this.focusInput();
            }
            this.update('this.selectedBubbleIndex');
        }
        return;
    }

    if (event.code === "Enter") {
        if (this.focusedOption && this.itemListVisible) {
            // If there's a focused option in dropdown, select it
            event.preventDefault();
            this.chooseOption(this.focusedOption);
            return;
        } else if (this.searchText.trim()) {
            // If there's text but no dropdown, create a bubble from the text
            event.preventDefault();
            this.handleCommaInput(); // Reuse the same logic as comma
            return;
        }
    }

    if (event.code === "Tab") {
        if (this.focusedOption && this.itemListVisible) {
            event.preventDefault();
            this.chooseOption(this.focusedOption);
            return;
        }
    }

    if (event.code === "Comma" || event.key === ",") {
        event.preventDefault();
        this.handleCommaInput();
        return;
    }

    // Clear bubble selection when typing
    if (this.selectedBubbleIndex >= 0 && event.key.length === 1) {
        this.selectedBubbleIndex = -1;
        this.update('this.selectedBubbleIndex');
    }
}

    handleCommaInput() {
        const searchText = this.searchText.trim();
        if (searchText) {
            // Create a new item from the current text
            const newItem = {
                name: searchText,
                value: searchText,
                isCustom: true
            };
            this.addSelectedItem(newItem);
            this.clearSearchInput();
        }
    }

    handlePaste(event) {
        event.preventDefault();
        const paste = (event.clipboardData || window.clipboardData).getData('text');
        const items = paste.split(/[,;\n\r]+/).map(item => item.trim()).filter(item => item);
        
        items.forEach(itemText => {
            const existingOption = this.options.find(opt => 
                opt.name.toLowerCase() === itemText.toLowerCase() || 
                opt.value.toLowerCase() === itemText.toLowerCase()
            );
            
            if (existingOption && !this.isAlreadySelected(existingOption)) {
                this.addSelectedItem(existingOption);
            } else if (!existingOption) {
                const newItem = {
                    name: itemText,
                    value: itemText,
                    isCustom: true
                };
                this.addSelectedItem(newItem);
            }
        });
        
        this.clearSearchInput();
    }

    getCursorPosition() {
        if (!this.searchInput) return 0;
        
        const selection = window.getSelection();
        if (selection.rangeCount === 0) return 0;
        
        const range = selection.getRangeAt(0);
        const preCaretRange = range.cloneRange();
        preCaretRange.selectNodeContents(this.searchInput);
        preCaretRange.setEnd(range.endContainer, range.endOffset);
        return preCaretRange.toString().length;
    }

    setCursorPosition(position) {
        if (!this.searchInput) return;
        
        const range = document.createRange();
        const sel = window.getSelection();
        
        if (this.searchInput.childNodes.length > 0) {
            const textNode = this.searchInput.childNodes[0];
            const maxPosition = textNode.textContent.length;
            const safePosition = Math.min(position, maxPosition);
            range.setStart(textNode, safePosition);
            range.setEnd(textNode, safePosition);
        } else {
            range.setStart(this.searchInput, 0);
            range.setEnd(this.searchInput, 0);
        }
        
        sel.removeAllRanges();
        sel.addRange(range);
    }

    // Option selection
    chooseOption(option) {
        this.addSelectedItem(option);
        this.clearSearchInput();
        this.hideOptions();
        this.focusInput();
    }

    addSelectedItem(item) {
        if (!this.isAlreadySelected(item)) {
            this.chosenItems.push(item);
            this.update('this.chosenItems');
            this.fire('change', this.value);
            this.performSearch(); // Refresh available options
        }
    }

    clearSearchInput() {
        if (this.searchInput) {
            this.searchInput.textContent = '';
        }
        this.searchText = '';
        this.performSearch();
    }

    // Navigation and focus
    focusInput() {
        if (this.searchInput) {
            this.searchInput.focus();
        }
        this.selectedBubbleIndex = -1;
        this.update('this.selectedBubbleIndex');
    }

    getChildIdx(option) {
        return this.foundOptions.findIndex((o) => o == option);
    }

    getOptionElement(option) {
        let childIdx = this.getChildIdx(option);
        if (this.itemlist && this.itemlist.children) {
            return this.itemlist.children[childIdx];
        }
        return null;
    }

    focusOption(option, scrollToTop = true) {
        this.removeFocusedElement();
        
        if (this.itemlist != null && option != null) {
            var childEl = this.getOptionElement(option);
            if (!childEl) {
                return;
            }
            if (scrollToTop) {
                this.itemlist.scrollTop = childEl.offsetTop;
            } else {
                this.scrollElementVisible(childEl);
            }
            childEl.classList.add('focus');
            this.focusedElement = childEl;
        }
        this.focusedOption = option;
        this.update('this.focusedOption');
    }

    removeFocusedElement() {
        if (this.focusedElement) {
            this.focusedElement.classList.remove('focus');
            this.focusedElement = null;
        }
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

    // Dropdown visibility
    showOptions() {
        if (!this.itemListVisible) {
            this.itemListVisible = true;
            this.update('this.itemListVisible');
            this.removeFocusedElement();
            if (this.foundOptions.length > 0) {
                this.focusOption(this.foundOptions[0]);
            }
        }
    }

    hideOptions() {
        this.itemListVisible = false;
        this.update('this.itemListVisible');
    }

    toggleOptions() {
        if (this.itemListVisible !== true) {
            this.showOptions();
        } else {
            this.hideOptions();
        }
    }

    // Event handlers
    elementKeydown(event) {
        if (this.readonly) {
            return;
        }
        
        if (event.code === "Escape") {
            this.hideOptions();
            return;
        }

        this.handleNavKeys(event);
    }

    handleNavKeys(event) {
        if (event.code === "ArrowUp" || event.code === "ArrowDown") {
            if (this.itemlist != null && this.foundOptions) {
                event.preventDefault();
                const currentIndex = this.focusedOption ? this.foundOptions.indexOf(this.focusedOption) : -1;
                
                if (event.code === "ArrowDown") {
                    if (currentIndex < this.foundOptions.length - 1) {
                        const nextOption = this.foundOptions[currentIndex + 1];
                        this.focusOption(nextOption, false);
                    }
                } else if (event.code === "ArrowUp") {
                    if (currentIndex > 0) {
                        const previousOption = this.foundOptions[currentIndex - 1];
                        this.focusOption(previousOption, false);
                    }
                }
            }
        }
    }

    clickedOption(option) {
        this.chooseOption(option);
        if (option.fn) {
            option.fn(option);
        }
    }

    handleBlur(event) {
        this.hasfocus = false;
        window.setTimeout(() => {
            if (!this.hasfocus) {
                this.hideOptions();
                this.selectedBubbleIndex = -1;
                this.update('this.selectedBubbleIndex');
            }
        }, 300);
    }

    handleFocus(event) {
        this.hasfocus = true;
        this.update('this.hasfocus');
    }

    handleMousedown(event) {
        this.hasfocus = true;
        event.stopPropagation();
        event.preventDefault();
    }

    focus() {
        this.focusInput();
    }

    // Utility methods
    touched() {
        this._touched = true;
    }

    render(browserElement) {
        return browserElement;
    }
}