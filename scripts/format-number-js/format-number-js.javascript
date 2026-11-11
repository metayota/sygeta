class NumberFormatterJS extends Tag {
    static {
        NumberFormatterJS.decimalSeparator = translate('number_format_decimal_separator')
        NumberFormatterJS.digitGroupSeparator = translate('number_format_group_separator')
        window.formatNumber = function formatNumber(number, decimalPlaces = 0) {
            let groupSize = 3
            let decimalSeparator = NumberFormatterJS.decimalSeparator
            let digitGroupSeparator = NumberFormatterJS.digitGroupSeparator
            number = number * 1
            number = number.toFixed(decimalPlaces);
            let parts = number.toString().split(".");
            parts[0] = parts[0].replace(new RegExp(`\\B(?=(\\d{${groupSize}})+(?!\\d))`, 'g'), digitGroupSeparator);
            return parts.join(decimalSeparator);
        }
    }
}