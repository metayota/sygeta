class PartnerRegistration extends Tag {
    init() {
        this.invitation_key = window.location.hash.substr(1)
        this.getInfo()
        this.partnerRegistrationEl
    }
	registerUser(user) {
        user.invitation_key = this.invitation_key
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

    getInfo() {
        resource.action('get_info',{invitation_key:this.invitation_key}).then(result=>{
            this.setAttribute('invitedUser', result.contact)
            if (result.mailReadonly) {
                let mailParameter = this.partnerRegistrationEl?.parameterEl?.parameters?.find(v=>v.name=='mail')
                mailParameter.readonly = true
                this.partnerRegistrationEl?.parameterEl?.update('this.parameters')
            }
        })
    }
}