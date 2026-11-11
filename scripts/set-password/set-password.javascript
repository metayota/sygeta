class ResetPasswordTag extends Tag {
    resetPw(data) {
        
        resource.action('reset_pw',Object.assign(data,{'token':router$.params.tab})).then(result=> {
            toast$.show(result.message,true,result.type)
        })
    }
}