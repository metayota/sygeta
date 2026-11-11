<?php namespace Metayota; include_once('../metayota/library.php');  ?><div class="form-element-container dropdown-form-element-container {this.class} form-box">
    <div class="dropdown-filter">
        <label class="form-label" if="this.label !== undefined">{translate(this.label)}</label>
        <div element="dropdownform" 
             (blur)="this.handleBlur(event)" 
             (focus)="this.handleFocus(event)"
             class="form-element filter-form-element {this.itemListVisible ? 'focus' : ''} {this.chosenItems.length == 0 ? 'default-option' : ''}"
             tabindex="0" 
             (click)="this.focusInput()" 
             (keydown)="this.elementKeydown(event)">
            
            <!-- Selected items as bubbles -->
            <div class="selected-items-container">
                <!-- Bubble container - the inner elements will repeat -->
                <div class="chosen-items" for="item of this.chosenItems">
                    <div class="selected-item-bubble {this.selectedBubbleIndex == item_index ? 'selected' : ''}" 
                         (click)="this.selectBubble(item_index)">
                        <span class="bubble-text">{item.name}</span>
                        <span class="bubble-remove" (click)="this.removeBubble(item_index, event)">×</span>
                    </div>
                    <!-- Editable input -->
                </div>
                <div class="filter-dropdown-search-input" 
                    contenteditable="{this.multiple || this.chosenItems?.length == 0}"  
                    element="searchInput" 
                    (input)="this.performSearch(event)" 
                    (keydown)="this.handleInputKeydown(event)"
                    (paste)="this.handlePaste(event)"
                    type="text"></div>

            </div>
        </div>
        
        <!-- No results message -->
        <div class="no-results-message" if="this.searchText && this.foundOptions?.length == 0">
            <div><t>no_matching_item_found</t></div>
        </div>
        
        <!-- Options dropdown -->
        <div class="itemlist-container" if="this.foundOptions?.length > 0">
            <div (mousedown)="this.handleMousedown(event)" 
                 element="itemlist"
                 class="{this.itemListVisible ? 'dropdown-options dropdown-options-visible' : 'dropdown-options'}">
                <!-- Option container - the inner elements will repeat -->
                <div for="opt of this.foundOptions">
                    <div (click)="this.clickedOption(opt)"
                         class="dropdown-option {opt?.isPlaceholder ? 'dropdown-placeholder' : ''} {this.focusedOption == opt ? 'focus' : ''}">
                        {opt.name}
                    </div>
                </div>
            </div>
        </div>

        <div class="clear"></div>
        <p class="error-message" if="!!this.errorMessage">{this.errorMessage}</p>
    </div>
</div>