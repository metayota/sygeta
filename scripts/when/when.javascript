class JWhen extends Tag {
    set test(t) {
        if (this._test != t) {
            this._test = t
            if (t) {
                this.call(t)
            }
        }
    }

    get test() {
        return this._test
    }
    set when(w) {
        if (this._when != w) {
            this._when = w
          //  if (w) {
                this.call(w)
          //  }
        }
    }

    get when() {
        return this._when
    }
    set then(t) {
        this.thenExpression = new Expression(t, {jscontext:true, tagAndvalue:true})
        this._then = t
        if (this._test) {
            this.call(this._test)
        }
        if (this._when !== undefined) {
            this.call(this._when)
        }
    }
    get then() {
        return this._then
    }
    call(value) {
        if (this.thenExpression) {
            try {
                this.thenExpression.callWithTagAndValue(this.parentTag,value)
            } catch(error) {
                console.error('Error in when',error)
            }
        }
    }
}