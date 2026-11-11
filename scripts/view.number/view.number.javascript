class ViewNumber extends Tag {
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

    updateValueDisplay(inputString, input = null, keepSelection = true) {
        const inputValue = String(inputString);
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
            if (shouldBeSeparator && !processedValue.startsWith(this.group_separator)) {
                processedValue = this.group_separator + processedValue;
            }
            let decimal_separator = this.decimal_separator;
            let regex = new RegExp(`[\\d\\${decimal_separator}-]`, 'g');

            if (charAtIndex.match(regex)) {
                processedValue = charAtIndex + processedValue;
                if (charAtIndex !== '-') processedNumber++;
            }
        }

        if (isNegative && !processedValue.startsWith('-')) {
            processedValue = '-' + processedValue;
        }

        if (this.max_decimal_places) {
            let decimalIndex = processedValue.indexOf(this.decimal_separator);
            if (decimalIndex !== -1) {
                let decimalPart = processedValue.substring(decimalIndex + 1);
                if (decimalPart.length > this.max_decimal_places) {
                    processedValue = processedValue.substring(0, decimalIndex + 1 + this.max_decimal_places);
                }
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
                    
                    let trimmedDecimalPart = decimalPart.replace(/0+$/, '');
                    let significantDecimalPlaces = trimmedDecimalPart.length;
                    
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
                
                let trimmedDecimalPart = decimalPart.replace(/0+$/, '');
                let significantDecimalPlaces = trimmedDecimalPart.length;
                
                let targetDecimalPlaces = Math.max(significantDecimalPlaces, this.decimal_places);
                if (this.max_decimal_places) {
                    targetDecimalPlaces = Math.min(targetDecimalPlaces, this.max_decimal_places);
                }
                
                return (value * 1).toFixed(targetDecimalPlaces).replace('.', this.decimal_separator);
            }

            return (value * 1).toFixed(this.decimal_places * 1).replace('.', this.decimal_separator);
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

        let newValue = this.updateValueDisplay(valueToDisplay, input, keepSelection)
        let suffix = this.getSuffix();

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

    set value(value) {
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
        let formattedValue = this.valueToDisplayValue(this._value, null, true, false);
        this.setAttribute('formattedValue', formattedValue)
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

    set suffix(s) {
        this._suffix = s
        this.update('this.suffix')
        if (this._value) {
            let formattedValue = this.valueToDisplayValue(this._value);
            this.setAttribute('formattedValue', formattedValue)
        }
    }

    get suffix() {
        return this._suffix
    }

    // Alle anderen Setter/Getter von NumberInputCmp übernehmen
    set max_decimal_places(f) {
        if (f) {
            this._max_decimal_places = f
            let formattedValue = this.valueToDisplayValue(this._value);
            this.setAttribute('formattedValue', formattedValue)
        }
    }
    get max_decimal_places() {
        return this._max_decimal_places
    }

    set decimal_places(f) {
        if (f) {
            this._decimal_places = f
            let formattedValue = this.valueToDisplayValue(this._value);
            this.setAttribute('formattedValue', formattedValue)
        }
    }
    get decimal_places() {
        return this._decimal_places
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