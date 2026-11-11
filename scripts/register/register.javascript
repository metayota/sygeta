class Registration extends Tag {
	registerUser(user) {
        user.timezone = Intl.DateTimeFormat().resolvedOptions().timeZone;
		resource.action( 'register', user ).then( result => {
            if (result.message != undefined) {
                toast$.show(result.message, true, result.type) 
            }
            if (result.success == true) {
                router$.goto( this.goto ? this.goto : '/thank-you' );
                this.fire(registered)
            }
        })
	}
}