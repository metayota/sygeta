class SignInWithGoogle extends Tag {
    init() {
        Tag.load('https://accounts.google.com/gsi/client').then(result => {

        });
        window.signInCallback = function (response) {
            if (response.credential) {
                var idToken = response.credential;
                resource.call({ 'id_token': idToken }).then(result => {
                    window.location = '/login';
                })
            }
        }
    }

    render() {
        window.google.accounts.id.renderButton(this.btn, {
        theme: 'outline',
        size: 'large',
        type: 'standard',
        text: 'signin_with',
        });
    }

    getCookie(name) {
        var nameEQ = name + "=";
        var ca = document.cookie.split(';');
        for (var i = 0; i < ca.length; i++) {
            var c = ca[i];
            while (c.charAt(0) == ' ') c = c.substring(1, c.length);
            if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length, c.length);
        }
        return null;
    }

    
}