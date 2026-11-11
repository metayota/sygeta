class AdminLoginAsUser extends Tag {
    login(data) {
        resource.call(data).then(result=> {
            if (result.success) {
                window.location = '/'
            }
        })
    }
}