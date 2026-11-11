class RCProfile extends Tag {
    setup() {
        resource.action('get_profile').then(profile=> {
            this.setAttribute('profile',profile)
        })
        resource.action('get_partner_profile').then(partnerProfile=> {
            this.setAttribute('partnerProfile',partnerProfile)
        })
    }
    updateUserProfile(data) {
        resource.action('update_user',data).then(()=>{
            toast$.show('User profile updated successfully',true,'success')
        })
    }

}