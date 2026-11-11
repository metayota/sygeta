/* VERSION: 1.1.13 */
class NumberInputCmp extends RCBaseFormElement {
   setup() {
       this.decimal_separator = translate('number_format_decimal_separator').length == 1 ? translate('number_format_decimal_separator') : '.';
       this.group_separator = translate('number_format_group_separator').length == 1 ? translate('number_format_group_separator') : ' ';
       this.group_separator_characters_count = 3;
       this.suffixSeparator = ' ';
       this._originalValue = null;
   }

   getValueFromFormattedInput(inputValue) {
       if (inputValue == null) {
           return '';
       }
       let inputValueStr = `${inputValue}`;
       let decimal_separator = this.decimal_separator;
       let regex = new RegExp(`[-\\d\\${decimal_separator}]+`, 'g');
       let matches = inputValueStr.match(regex);
       let joined = matches ? matches.join('') : '';
       let convertedValue = joined.replace(this.decimal_separator, '.')
       return convertedValue;
   }

   handleKeypress(input, inputValue, event) {
       this._originalValue = null;
       if (event.keyCode == 13) {
           this.valueToDisplayValue(inputValue, input, false, true);
       } else {
           this.correctSelection(inputValue, input);
           if (event.keyCode == 8) {
               if (inputValue.charAt(input.selectionStart - 1) == this.group_separator) {
                   input.selectionStart = input.selectionStart - 1;
                   input.selectionEnd = input.selectionEnd - 1;
               }
           }
       }
   }

   onBlur(node, event) {
       this.valueToDisplayValue(node.value, node, true, false);
       this.onChange(event, node.value);
       this.isValid()
   }

   updateValueDisplay(inputString, input = null, keepSelection = true) {
       const inputValue = String(inputString);
       const selectionStart = (input != null) ? input.selectionStart : 0;
       const selectionEnd = (input != null) ? input.selectionEnd : 0;

       let newSelectionStart = 0;
       let newSelectionEnd = 0;
       let processedValue = '';
       let processedNumber = 0;
       let integerPart = inputValue.indexOf(this.decimal_separator) === -1 ? true : false;
       let isNegative = inputValue.startsWith('-');

       for (let i = inputValue.length - 1; i >= 0; i--) {
           const charAtIndex = inputValue.charAt(i);
           if (charAtIndex === this.decimal_separator) {
               processedNumber = -1;
               integerPart = true;
           }
           const shouldBeSeparator = integerPart && (((processedNumber % this.group_separator_characters_count) === 0) && processedNumber > 0);
           const isSeparator = (charAtIndex === this.group_separator) ||
               (Number(charAtIndex).toString() === 'NaN' && charAtIndex !== '-') ||
               (charAtIndex === this.decimal_separator);
           const isSelectionStart = (i === selectionStart);
           const isSelectionEnd = (i === selectionEnd);

           if (shouldBeSeparator && !processedValue.startsWith(this.group_separator)) {
               processedValue = this.group_separator + processedValue;
               newSelectionStart++;
               newSelectionEnd++;
           }
           let decimal_separator = this.decimal_separator;
           let regex = new RegExp(`[\\d\\${decimal_separator}-]`, 'g');

           if (charAtIndex.match(regex)) {
               processedValue = charAtIndex + processedValue;
               newSelectionStart++;
               newSelectionEnd++;
               if (charAtIndex !== '-') processedNumber++;
           }

           if (isSelectionStart) {
               newSelectionStart = 0;
           }
           if (isSelectionEnd) {
               newSelectionEnd = 0;
           }
       }

       if (isNegative && !processedValue.startsWith('-')) {
           processedValue = '-' + processedValue;
           newSelectionStart++;
           newSelectionEnd++;
       }

       if (this.max_decimal_places) {
           let decimalIndex = processedValue.indexOf(this.decimal_separator);
           if (decimalIndex !== -1) {
               let decimalPart = processedValue.substring(decimalIndex + 1);
               if (decimalPart.length > this.max_decimal_places) {
                   let oldLength = processedValue.length;
                   processedValue = processedValue.substring(0, decimalIndex + 1 + this.max_decimal_places);
                   let lengthDifference = oldLength - processedValue.length;

                   if (newSelectionStart > processedValue.length) {
                       newSelectionStart = processedValue.length;
                   }
                   if (newSelectionEnd > processedValue.length) {
                       newSelectionEnd = processedValue.length;
                   }
               }
           }
       }

       if (input != null) {
           if (keepSelection) {
               input.value = processedValue;
               input.selectionStart = newSelectionStart;
               input.selectionEnd = newSelectionEnd;
           }
       }
       return processedValue;
   }

 checkAndCorrectFractonSize(value) {
    if (!value) {
        return '';
    }
    
    if (this.decimal_places !== null && (this.decimal_places === null || this.decimal_places === 0 || value.endsWith(this.decimal_separator))) {
        return Math.round(value) + '';
    } else {
        if (this._originalValue !== null) {
            let valueStr = this._originalValue.toString();
            let decimalIndex = valueStr.indexOf('.');
            if (decimalIndex === -1) {
                decimalIndex = valueStr.indexOf(this.decimal_separator);
            }

            if (decimalIndex !== -1) {
                let decimalPart = valueStr.substring(decimalIndex + 1);
                
                // Trailing Nullen entfernen
                let trimmedDecimalPart = decimalPart.replace(/0+$/, '');
                let significantDecimalPlaces = trimmedDecimalPart.length;
                
                // Mindestens decimal_places anzeigen, aber nie mehr als max_decimal_places
                let targetDecimalPlaces = Math.max(significantDecimalPlaces, this.decimal_places);
                if (this.max_decimal_places) {
                    targetDecimalPlaces = Math.min(targetDecimalPlaces, this.max_decimal_places);
                }
                
                return (this._originalValue * 1).toFixed(targetDecimalPlaces).replace('.', this.decimal_separator);
            }
            
            return (this._originalValue * 1).toFixed(this.decimal_places * 1).replace('.', this.decimal_separator);
        }
        
        let valueStr = value.toString();
        let decimalIndex = valueStr.indexOf('.');
        if (decimalIndex === -1) {
            decimalIndex = valueStr.indexOf(this.decimal_separator);
        }

        if (decimalIndex !== -1) {
            let decimalPart = valueStr.substring(decimalIndex + 1);
            
            // Trailing Nullen entfernen
            let trimmedDecimalPart = decimalPart.replace(/0+$/, '');
            let significantDecimalPlaces = trimmedDecimalPart.length;
            
            // Mindestens decimal_places anzeigen, aber nie mehr als max_decimal_places
            let targetDecimalPlaces = Math.max(significantDecimalPlaces, this.decimal_places);
            if (this.max_decimal_places) {
                targetDecimalPlaces = Math.min(targetDecimalPlaces, this.max_decimal_places);
            }
            
            return (value * 1).toFixed(targetDecimalPlaces).replace('.', this.decimal_separator);
        }

        return (value * 1).toFixed(this.decimal_places * 1).replace('.', this.decimal_separator);
    }
}

   set max_decimal_places(f) {
       if (f) {
           this._max_decimal_places = f
           this.formattedValue = this.valueToDisplayValue(this._value, this.input, true);
           this.update('this.formattedValue')
       }
   }
   get max_decimal_places() {
       return this._max_decimal_places
   }

   correctSelection(value, input = null) {
       if (input == null) {
           return;
       }
       let suffix = this.getSuffix();
       if (value.endsWith(suffix)) {
           let maxSelectionEnd = value.length - suffix.length;
           if (input.selectionEnd > maxSelectionEnd) {
               input.selectionEnd = maxSelectionEnd;
           }
       } else {
           this.valueToDisplayValue(input.value, input, true, false);
       }
   }

   valueToDisplayValue(value, input = null, checkRange = false, keepSelection = true) {
       let valueToDisplay = String(value);
       let rawValue = this.getValueFromFormattedInput(valueToDisplay);
       if (this.max != undefined && rawValue != '' && ((1 * rawValue) > (1 * this.max))) {
           rawValue = this.max + ''
           valueToDisplay = this.checkAndCorrectFractonSize(rawValue);
       }
       if (checkRange) {
           if (!this.isNumberInRange(rawValue)) {
               rawValue = this.getNumberInRange(rawValue) + '';
           }
           valueToDisplay = this.checkAndCorrectFractonSize(rawValue);
       }

       if (keepSelection) {
           this.correctSelection(valueToDisplay, input);
       }

       let newValue = this.updateValueDisplay(valueToDisplay, input, keepSelection)
       let suffix = this.getSuffix();
       if (input != null) {
           let keepSelectionStart = input.selectionStart;
           let keepSelectionEnd = input.selectionEnd;
           if (input.value !== newValue + suffix) {
               input.value = newValue + suffix;
           }
           if (keepSelection) {
               input.selectionStart = keepSelectionStart;
               input.selectionEnd = keepSelectionEnd;
           }
       }

       return newValue + suffix;
   }

   getSuffix() {
       if (this.suffix && this.suffix != '') {
           if (window.Translation != undefined && Translation.translations[this.value + '_' + this.suffix] != undefined) {
               return this.suffixSeparator + translate(this.value + '_' + this.suffix);
           }
           return this.suffixSeparator + translate(this.suffix);
       } else {
           return '';
       }
   }

   onChange(e, value) {
       this._value = this.getValueFromFormattedInput(value).replace(this.decimal_separator, '.');
       this.fire('change', this._value);
   }

   set label(l) {
       this._label = l
       this.update('this.label')
   }
   get label() {
       return this._label
   }

   set suffix(s) {
       this._suffix = s
       this.update('this.suffix')
       this.update('this.getSuffix')
       if (this._value) {
           this.formattedValue = this.valueToDisplayValue(this._value);
           this.update('this.formattedValue')
       }
   }
   get suffix() {
       return this._suffix
   }

   set value(value) {
       this.formattedValue = ''
       if (value == undefined) {
           value = '';
           this._originalValue = null;
       } else {
           if (typeof value === 'number') {
               value = value + ''
           }
           if (value.includes('.') || value.includes(this.decimal_separator)) {
               this._originalValue = value.replace(this.decimal_separator, '.');
           } else {
               this._originalValue = null;
           }
       }
       this._value = value.replace('.', this.decimal_separator)
       this.formattedValue = this.valueToDisplayValue(this._value, this.input, true, false);
       this.update('this.formattedValue')
   }
   get value() {
       if (typeof this._value == 'string' && this._value != '') {
           return this._value.replace(this.decimal_separator, '.')
       } else if (this._value) {
           return this._value
       } else {
           if (this._min) {
               return this._min
           } else {
               return undefined
           }
       }
   }

   set decimal_places(f) {
       if (f) {
           this._decimal_places = f
           this.formattedValue = this.valueToDisplayValue(this._value, this.input, true);
           this.update('this.formattedValue')
       }
   }
   get decimal_places() {
       return this._decimal_places
   }

   set group_separator(f) {
       if (f) {
           this._group_separator = f
           this.formattedValue = this.valueToDisplayValue(this._value, this.input, true);
           this.update('this.formattedValue')
       }
   }
   get group_separator() {
       return this._group_separator
   }

   set decimal_separator(f) {
       if (f) {
           this._decimal_separator = f
           this.formattedValue = this.valueToDisplayValue(this._value, this.input, true);
           this.update('this.formattedValue')
       }
   }
   get decimal_separator() {
       return this._decimal_separator
   }

   set group_separator_characters_count(c) {
       this._group_separator_characters_count = c
       this.formattedValue = this.valueToDisplayValue(this._value, this.input, true);
       this.update('this.formattedValue')
   }
   get group_separator_characters_count() {
       return this._group_separator_characters_count
   }

   set name(n) {
       this._name = n
       this.update('this.name')
   }
   get name() {
       return this._name
   }

   set min(m) {
       this._min = m * 1
   }
   get min() {
       return this._min
   }

   isNumberInRange(value) {
       if (value != null) {
           value = value * 1
           if (this.min != null) {
               if (value < this.min) {
                   return false;
               }
           }
           if (this.max != null) {
               if (value > this.max) {
                   return false;
               }
           }
       }
       return true;
   }

   getNumberInRange(value) {
       if (value != null) {
           value = value * 1;
           if (this.min !== null) {
               if (value < this.min) {
                   return this.min;
               }
           }
           if (this.max !== null) {
               if (value > this.max) {
                   return this.max;
               }
           }
       }
       return value;
   }
}