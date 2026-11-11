class RegExValidator {
    isValid(value) {
        if (value.match('^' + this.regex + '$') === null) {
            return this.title
        } else {
            return true
        }
    }
}