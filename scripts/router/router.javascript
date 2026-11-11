class RouterTag extends Tag {
    
    static initialize() { window.router$ =  {} }
    init() {
        if (!this.base)
            this.base = ''
        this.publish('router$', this);
        this.matchAndUpdate(window.location.pathname + window.location.hash) //
        window.onpopstate = function(event) {
            this.matchAndUpdate(document.location.pathname, false )//document.location.hash
        }.bind(this)
    }
    goto(page) {
        this.matchAndUpdate(this.base + page);
    }
    go(pageName, params = {}) {
        let route = this.routeByName(pageName);
        this.gotoRoute(route, params);
    }
    routeByName(name) {
        return this.routes.find(v => v.name == name);
    }
    gotoRoute(route, vars, pushState=true,hash="") {
        this.active = route;
        this.params = vars;
        this.attributes = Object.assign({},route,vars)
        this.activeUrl = route.url;
        this.activeRoute = route.name;
        let url = route.url
        for (let varName in vars) {
            let varValue = vars[varName]
            url = url.split('[' + varName + ']').join(varValue)
        }
        if (pushState) {
            window.history.pushState({}, "", this.base + url + hash); 
        }
        this.fire('change');
        
        notify('router$');
        this.fire('changed');
        document.scrollingElement.scrollTop = 0
        if (route.title != undefined) {
            document.title = route.title;
        }


    }

    extractHash(url) {
        const hashIndex = url.indexOf('#');
        return hashIndex !== -1 ? url.substring(hashIndex) : '';
    }
    matchAndUpdate(page,pushState=true) {
        for (let route of this.routes) {
            let vars = null;
            if (vars = this.matches(this.base + route.url, page)) {
                this.gotoRoute(route, vars, pushState, this.extractHash(page));
                return;
            }
        }
        this.active = null;
        this.update('router$');
    }

    matches(path, url) {
        url = url.split('#')[0]
        let pathElements = path.split('/')// path.split(/[/\.]/);
        let urlElements = url.split('/')//url.split(/[/\.]/);
        
        let variables = {};
        for (let pathElementIdx in pathElements) {
            let pathElement = pathElements[pathElementIdx];
            let urlElement = urlElements[pathElementIdx];
            if (urlElement == undefined) {
                return false;
            }
            if (pathElement.startsWith('[')) {
                let varName = pathElement.substr(1, pathElement.length - 2);
                variables[varName] = urlElement;
            } else {
                if (pathElement != urlElement) {
                    return false;
                }
            }
        }
        if (variables.length == 0) {
            return true;
        }
        return variables;
    }
}