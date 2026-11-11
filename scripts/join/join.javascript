class JoinTag extends Tag {

    init() {
        if (window.self === window.top) {
            JoinTag.invite_id = router$.params.invite_id
            resource.action('count_click',{invite_id:JoinTag.invite_id})
        }
    }

    saveCookie() {
        if (JoinTag.invite_id != undefined) {
            let date = new Date();
            date.setMonth(date.getMonth() + 1);
            document.cookie = "invite_id="+JoinTag.invite_id+"; expires=" + date.toUTCString() + "; path=/";
        }
    }

}