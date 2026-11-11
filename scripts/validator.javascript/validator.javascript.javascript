class JSValidator {
    isValid(value) {
        if (eval(this.expression) === true) {
            return true;
        } else {
            return this.title;
        }
    }
}