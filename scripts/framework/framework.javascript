class Subscriptions { 
    constructor() {
        this.subscriptions = {};
        this.subscribers = [];
    }
    static getMainSubscription() {
        return this.mainSubscription;
    }
    subscribe(method, objectPath = []) {
        if (objectPath.length == 0) {
            this.subscribers.push(method);
            return this;
        } else {
            let path = objectPath[0];
            objectPath.splice(0, 1);
            if (!this.subscriptions[path]) {
                this.subscriptions[path] = new Subscriptions();
            }
            return this.subscriptions[path].subscribe(method, objectPath);
        }
    }

    unsubscribe(method, objectPath = []) {
        if (objectPath.length == 0) {
            this.subscribers.splice(this.subscribers.indexOf(method), 1);
            return this;
        } else {
            let path = objectPath[0];
            objectPath.splice(0, 1);
            if (this.subscriptions[path]) {
                this.subscriptions[path].unsubscribe(method, objectPath);
            }
            return;
        }
    }
    notifySubscribers() {
        for (let subscriber of this.subscribers) {
            subscriber.changed();
        }
        for (let subscription in this.subscriptions) {
            this.subscriptions[subscription].notifySubscribers();
        }
    }
    notify(pathElements) {
        if (pathElements.length > 0) {
            let path = pathElements[0];
            pathElements.splice(0, 1);
            if (this.subscriptions[path]) {
                this.subscriptions[path].notify(pathElements);
            }
        } else {
            this.notifySubscribers();
        }
    }
}
Subscriptions.mainSubscription = new Subscriptions()
var mainSubscription = Subscriptions.getMainSubscription()

function notify(path) {
    mainSubscription.notify(path.split('.'));
}

function subscribe(path, method) {
    return mainSubscription.subscribe(method, path.split('.'));
}

function unsubscribe(path, method) {
    mainSubscription.unsubscribe(method, path.split('.'));
}
class BrowserElementSubscription {
    constructor(attribute, browserElement, subscription) {
        this.attribute = attribute;
        this.browserElement = browserElement;
        this.subscription = subscribe(subscription, this);
    }
    changed() {
        this.attribute.renderAttribute(this.browserElement, { tag: this.browserElement.tag });
    }
    destroy() {
        this.subscription.unsubscribe(this);
    }
}
class TagParameterSubscription {
    constructor(tag, path, expression, subscription) {
        this.tag = tag
        this.path = path
        this.expression = expression
        this.subscription = subscribe(subscription, this)
        let code = '( function(tag,result) { tag.' + path + ' = result; } )';
        this.set = eval(code)
    }
    changed() {
        var tag = this.tag
        var result = this.expression.callWithTagAndElement(tag.parentTag, tag)
        this.set(tag, result)
    }
    destroy() {
        this.subscription.unsubscribe(this);
    }
}
class BrowserElement {
    constructor(node) {
        this.node = node;
        this.init();
    }
    init() {
        this.children = [];
        this.subscriptions = [];
        this.tagSubscriptions = [];
        this.modelElement = null;
    }
    reset() {
        this.destroy(false);
        this.init();
    }
    bind(attribute, subscriptionStr = null) {
        if (subscriptionStr !== null) {
            let subscription = new BrowserElementSubscription(attribute, this, subscriptionStr);
            this.subscriptions.push(subscription);
        } else {
            if (this.boundAttributes == null) {
                this.boundAttributes = [];
            }
            this.boundAttributes.push(attribute);
        }
    }
    update(options) {
        if (this.modelElement !== null) {
            this.modelElement.update(this, options);
        }
    }
    static replaceElement(el, withEl) {
        el.parentNode.insertBefore(withEl, el);
        el.remove();
    }
    replaceElement(withEl) {
        if (this.node) {
            if (this.node.parentNode) {
                this.node.parentNode.insertBefore(withEl, this.node); // Insert BEFORE not after
            }
            this.node.remove()
        }
        this.node = withEl
    }
    render(options) {
        this.modelElement.render(options, this);
    }
    removeNode() {
        if (this.node) {
            this.node.remove();
        }
        this.node = null;
    }
    destroySubitems() {
        if (this.children != null) {
            for (let child of this.children) {
                if (child != undefined) { //why undefined?
                    child.destroy(false);
                }
            }
        }
        this.children = [];
    }
    destroy(withNode = true) {

        this.destroySubitems();
        if (withNode && this.node) {
            this.node.remove();
            this.node = undefined;
        }
        if (this.subscriptions && this.subscriptions.length > 0) {
            for (let subscription of this.subscriptions) {
                subscription.destroy();
            }
        }
        if (this.tagSubscriptions && this.tagSubscriptions.length > 0) {
            for (let tagSubscription of this.tagSubscriptions) {
                tagSubscription.tag.unsubscribe(tagSubscription.name, this)
            }
        }
        this.tagSubscriptions = undefined;
        this.subscriptions = undefined;
        if (this.boundAttributes != null && this.boundAttributes.length > 0) {
            for (let boundAttribute of this.boundAttributes) {
                boundAttribute.unsubscribe(this);
            }
        }

        if (this.ownsTag !== undefined) {
            this.ownsTag.destroy(); // ownsTag destroy the tag (include tag)
            // here is a bug for the include tag (gets destroyed)
        }
    }
    replaceBrowserElement() {

    }
    unplace(comment = '') {
        if (this.node !== null && this.node.nodeType !== 8) {
            this.place(document.createComment(comment ? comment : 'placeholder (unplaced):'))
            this.destroySubitems()
        }
    }
    place(newNode) {
        if (newNode !== null) {
            this.replaceElement(newNode);
        } else {
            this.unplace();
        }
    }
}
var count = 0;
class ModelElement {
    constructor() {
        this.attributes = [];
        this.children = [];
        this.tagModel = null;
    }
    parseAttributes(node, options) {
        let attribs = node.attributes;

        if (attribs !== undefined) {
            for (var i = 0; i < attribs.length; i++) {
                let attrib = attribs[i];
                //if (!Attribute.attributeTypeNames.some( v => v == attrib.name)) continue;
                if (node.nodeName == 'LABEL' && attrib.name == 'for') continue;

                let attribute = Attribute.parseAttribute(this.modelNode, attrib.name, attrib.value, options);
                if (attribute !== null) {
                    this.attributes.push(attribute);
                }
            }
        }
        if (node.nodeType == 3 && node.nodeValue != null && Attribute.containsExpressions(node.nodeValue)) {
            this.attributes.push(new TextNodeAttribute("textnode", node.nodeValue, options));
        }
    }
    parseChildren(node, options) {
        if (node.hasChildNodes()) {
            let childNodes = Array.from(node.childNodes);
            for (let childNode of childNodes) {
                let childModelElement = null;

                let idx = 0;
                if (this.attributes.length == 0) {
                    idx = this.children.length + (options.childIdx !== undefined ? options.childIdx : 0)
                } else {
                    idx = this.children.length;
                }

                if (childNode.nodeType === 3) {
                    let trimmed = childNode.nodeValue.trim();
                    if (trimmed) {
                        if (Attribute.containsExpressions(trimmed)) {
                            if (node.nodeName == 'TEXTAREA') {
                                this.attributes.push(new TextareaNodeAttribute("textareanode", childNode.nodeValue, options))
                            } else {
                                childModelElement = new ModelElement(); //it shouldn't parse this
                                childModelElement.modelNode = document.createTextNode('');
                                childModelElement.attributes.push(new TextNodeAttribute("textnode", childNode.nodeValue, options));
                            }
                        } else {
                            this.modelNode.appendChild(childNode);
                            childModelElement = null;
                        }
                    }

                } else if (childNode.nodeType === 1) {
                    childModelElement = new ModelElement();
                    childModelElement.parse(childNode, Object.assign({}, options, { childIdx: idx }));

                    if (childModelElement.tagModel !== null) { } else if (childModelElement.attributes != null && childModelElement.attributes.length > 0) { } else if (childModelElement.children != null && childModelElement.children.length > 0) {
                        this.modelNode.appendChild(childModelElement.modelNode);
                        for (let child of childModelElement.children) {
                            this.children.push(child);
                        }
                        childModelElement = null;
                    } else {
                        if (childModelElement.modelNode) {
                            this.modelNode.appendChild(childModelElement.modelNode);
                        } else { }
                        childModelElement = null;
                    }
                }

                if (childModelElement !== null) {
                    let childElNode = document.createElement('child');
                    childElNode.setAttribute('i', idx);
                    this.modelNode.appendChild(childElNode);

                    this.children.push(childModelElement);
                }
            }
        }
    }
    subscribeAttributes(browserElement, options) {
        for (let attribute of this.attributes) {
            attribute.subscribe(browserElement, options);
        }
    }
    renderAttributes(browserElement, options) {
        options.render = true
        for (let attribute of this.attributes) {
            if (options.render !== false) {
                attribute.renderAttribute(browserElement, options);
            }
        }
    }
    renderChildren(browserElement, options) { //should also render children when it has no node..
        if (options.render !== false && options.renderChildren !== false) {
            if (browserElement.node && (browserElement.node.nodeType == 1 || browserElement.node.nodeType == 11)) {
                if (this.children.length > 0) {
                    const childElements = Array.from(browserElement.node.querySelectorAll('child'));
                    for (let i = childElements.length - 1; i >= 0; i--) {
                        let childElement = childElements[i];
                        let childBrowserEl = this.children[childElement.getAttribute('i')].render(Object.assign({}, options), null);
                        browserElement.children.push(childBrowserEl);
                        if (childBrowserEl != null && childBrowserEl.node != null) {
                            BrowserElement.replaceElement(childElement, childBrowserEl.node);
                        } else {
                            childElement.remove();
                        }
                    }
                }
            }
        }
    }
    parseAttributesAndExpressions(node, path, expressions, options) {
        let attrs = null
        let events = {}
        let tagReference = undefined

        if (node.nodeType == 3) {

        } else {
            let isArray = false;
            let isText = false;
            if (node.attributes !== undefined) {
                for (let attribute of node.attributes) {
                    let attributeName = attribute.nodeName;
                    let attributeValue = attribute.nodeValue;
                    if (attributeName === 'text') {
                        if (attrs == null) {
                            attrs = {}
                        }
                        isText = true
                        if (attributeValue) {
                            attrs[attributeValue] = node.innerHTML;
                        } else {
                            attrs = node.innerHTML;
                        }
                    } else if (attributeName === 'array') {
                        isArray = true
                    } else if (attributeName.startsWith('(')) {
                        let eventName = attributeName.substr(1, attributeName.length - 2)
                        // todo, do not subscribe to attribute options
                        events[eventName] = new Expression(attributeValue, Object.assign({}, options, { returnVoid: true, jscontext: true }));
                    } else if (attributeName == 'element') {
                        tagReference = attributeValue
                    } else {
                        if (attrs == null) {
                            attrs = {}
                        }
                        if (Attribute.containsExpressions(attributeValue)) {
                            let newPath = path.slice();
                            newPath.push(attributeName)
                            expressions.push({ path: newPath.join('.'), expression: new Expression(attribute.nodeValue, options) })
                            attrs[attributeName] = null
                        } else {
                            attrs[attributeName] = attributeValue
                        }
                    }
                }
            }
            if (!isText) {
                let iNode = 0;
                for (let child of node.childNodes) {
                    if (child.nodeType == 3) {
                        let nodeValue = child.nodeValue.trim();
                        if (nodeValue) {
                            if (Attribute.containsExpressions(nodeValue)) {
                                let expr = new Expression(nodeValue, options)
                                expressions.push({ path: path.join('.'), expression: expr })
                                attrs = null
                            } else {
                                attrs = nodeValue
                            }
                        }
                    } else {
                        let childPath;
                        if (isArray === true) {
                            childPath = path.slice();
                            childPath.push(node.nodeName.toLowerCase() + '[' + iNode + ']')
                        } else {
                            childPath = path;
                        }
                        let result = this.parseAttributesAndExpressions(child, childPath, expressions, options);
                        if (result.attributes) {
                            if (attrs == null) {
                                if (isArray === true) {
                                    attrs = []
                                } else {
                                    attrs = {}
                                }
                            }
                            if (isArray === true) {
                                attrs.push(result.attributes)
                            } else {
                                attrs[child.nodeName.toLowerCase()] = result.attributes;
                            }
                        }
                        iNode++;
                    }

                }
            }
        }
        return { attributes: attrs, expressions: expressions, events: events, tagReference: tagReference };
    }
    parseTag(node, options) {
        if (node && node.tagName !== undefined && Tag.isRegistered(node.tagName.toLowerCase())) {

            this.tagModel = new TagModel(node.tagName.toLowerCase());
            let result = this.parseAttributesAndExpressions(node, [], [], options);

            this.tagModel.attributes = result.attributes;
            this.tagModel.expressions = result.expressions;
            this.tagModel.events = result.events;
            this.tagModel.tagReference = result.tagReference;
            return true;
        }
        return (this.tagModel != null);
    }
    parse(node, options) {
        if (!this.parseTag(node, options)) {
            this.modelNode = node.cloneNode(false);
            this.parseAttributes(node, options);

            let newOptions = Object.assign(options)
            if (newOptions.aliases != undefined) {
                newOptions.aliases = Array.from(options.aliases)
            }
            this.parseChildren(node, newOptions);
        }
    }
    updateChildren(browserElement, options) {
        if (browserElement.children != null) {
            for (let browserElementChild of browserElement.children) {
                if (browserElementChild.node != null) {
                    browserElementChild.update(options);
                    if (browserElementChild.removed) {
                        browserElementChild.place();
                    }
                } else {
                    browserElementChild.render(options);
                }
            }
        }
    }
    update(browserElement, options) {
        let newOptions = Object.assign({}, options, { subscribe: false, tag: browserElement.tag });
        this.renderAttributes(browserElement, newOptions);
        this.updateChildren(browserElement, newOptions);
    }
    _renderChildren(options, browserElement) {
        let clonedNode = document.importNode(this.modelNode, true)
        browserElement = new BrowserElement(clonedNode)
        browserElement.tag = options ? options.tag : undefined
        if (options.forIdx) {
            browserElement.forIdx = Object.assign({}, browserElement.forIdx, options.forIdx)
        }
        browserElement.modelElement = this;
        this.renderChildren(browserElement, options)
        return browserElement;
    }
    render(options, browserElement) {
        if (this.tagModel !== null) {
            browserElement = this.tagModel.render({ tag: options.tag, forIdx: options.forIdx }, browserElement)
        } else {
            let newNode = this.modelNode ? document.importNode(this.modelNode, true) : null

            if (browserElement == null) {
                browserElement = new BrowserElement(newNode);
            } else {
                browserElement.place(newNode)
            }
        }
        if (browserElement) {
            //browserElement.tag = options.tag;
            if (!browserElement.ownsTag) {
                browserElement.tag = options.tag;
            }
            browserElement.forIdx = Object.assign({}, browserElement.forIdx, options.forIdx); // todo: set on model element, if forIdx is needed
            browserElement.modelElement = this;
            if (options.renderAttributes !== false) {
                this.subscribeAttributes(browserElement, options); // 	todo: only when render attributes
                this.renderAttributes(browserElement, options); // todo: process if before import of node
            }
            this.renderChildren(browserElement, options);
        }
        return browserElement;
    }
    destroy() {
        for (let attribute of this.attributes) {
            attribute.destroy();
        }
        this.attributes = null;
        for (let child of this.children) {
            child.destroy();
        }
        this.children = null;
    }
}
class TagModel {
    constructor(tagname) {
        this.expressions = [];
        this.attributes = {};
        this.tagName = tagname;
        this.classObj = null;
        this.modelElement = null;
    }
    static renderTag(tagname, attributes, browserElement = null, options = {}, events) {
        let tagModel = new TagModel(tagname)
        tagModel.attributes = attributes
        // let events = {}
        /*  for (let attributeKey in attributes) {
                  if (attributeKey.startsWith('(')) {
                      let attributeValue = attributes[attributeKey]
                      let eventName = attributeKey.substr(1, attributeKey.length - 2)
                      events[eventName] = new Expression(attributeValue, Object.assign({}, options, { returnVoid: true, jscontext: true }));
                      tagModel.attributes[attributeKey] = undefined
                  }
              } */
        tagModel.events = events
        return tagModel.render(options, browserElement)
    }
    renderTag(options, browserElement) {
        if (this.classObj === null) {
            this.classObj = Tag.getClass(this.tagName);
            this.defaults = Tag.getDefaults(this.tagName)
            if (this.classObj === null)
                return null;

            if (this.modelElement === null) {
                this.modelElement = Tag.getModelElement(this.tagName);
            }
        }

        let classObj = this.classObj;
        let tag = new classObj()
        tag.tagModel = this
        tag.parentTag = options.tag
        tag.forIdx = options.forIdx // is this needed?

        let attrs = {}
        Object.assign(attrs, this.defaults, this.attributes)
        for (let expressionParam of this.expressions) {
            let expression = expressionParam.expression;

            let result = expression.callWithTagAndElement(tag.parentTag, tag);
            if (result !== undefined) {
                if (expressionParam.path != '') {
                    eval('attrs.' + expressionParam.path + ' = result');
                } else {
                    console.warn('expressionParam.path is empty')
                }
            }
            for (let subscription of expression.subscriptions) {
                let tagParamSubscription = new TagParameterSubscription(tag, expressionParam.path, expression, subscription);
            }
        }

        if (tag.setup) {
            tag.setup()
        }
        Object.assign(tag, attrs)
        if (tag.init) {
            tag.init();
        }

        options.tag = tag;
        if (this.tagReference !== undefined) {
            tag.parentTag[this.tagReference] = tag;
        }
        if (this.modelElement === null) {
            let newBrowserElement = null
            if (tag.render) {
                newBrowserElement = tag.render(browserElement); // rename and make default function to only process node
            }
            if (newBrowserElement) {
                this.subscribeForParameterChanges(tag, options)
                newBrowserElement.ownsTag = tag
            }
            if (tag.ready) {
                tag.ready()
            }
            return newBrowserElement;
        }
        if (this.modelElement !== null) {
            let newBrowserElement = this.modelElement.render({ tag: tag }, browserElement);
            tag.render(newBrowserElement);
            this.subscribeForParameterChanges(tag, options);
            newBrowserElement.ownsTag = tag
            tag.ready()
            return newBrowserElement;
        }
    }
    subscribeForParameterChanges(tag, options) {
        for (let expressionParam of this.expressions) {
            let expression = expressionParam.expression;
            for (let subscription of expression.subscriptions) {
                subscribe(subscription, tag);
            }
            let tagSubscriptions = expression.getTagSubscriptions(options)
            for (let subscriptionName of tagSubscriptions) {
                if (tag.parentTag) {
                    tag.parentTag.subscribeTagAttribute(subscriptionName, expressionParam.path, tag, expression)
                } else {
                    console.warn('Tag parentTag is not defined', tag)
                }
            }
        }
    }
    unsubscribeForParameterChanges(tag) {
        for (let expressionParam of this.expressions) {
            let expression = expressionParam.expression;
            for (let subscription of expression.subscriptions) {
                unsubscribe(subscription, tag);
            }
            for (let subscriptionName in expression.tagSubscriptions) {
                let subscription = expression.tagSubscriptions[subscriptionName]
                if (tag.parentTag) {
                    tag.parentTag.unsubscribeTagAttribute(subscription, expressionParam.path, tag)
                } else {
                    console.warn('Tag parentTag is not defined', tag)
                }
            }
        }
    }
    render(options, browserElement) {
        if (Tag.isReady(this.tagName)) {
            return this.renderTag(options, browserElement);
        }
        if (browserElement == null) {
            let tempNode = document.createComment('downloading tag "' + this.tagName + '"')
            browserElement = new BrowserElement(tempNode);
        }
        Tag.ready(this.tagName).then((ready) => {
            let newBrowserEl = this.renderTag(options, browserElement); //can return undefined
        });
        return browserElement;
    }
}
class GlobalResource {
    static call(name, parameters = null, useGet = false) {
        return Resource.action(name, null, parameters, useGet) //Tag.callJson(name, parameters)
    }
    static action(name, action, parameters = null, useGet = false) {
        return Resource.action(name, action, parameters, useGet)
    }
}
class ResourceObject {
    post(action = undefined, parameters = {}) {
        if (typeof action == 'object') {
            return this.call(action, false)
        } else if (action == undefined) {
            return this.call(parameters, false)
        } else {
            return this.action(action, parameters, false)
        }
    }
    get(action = undefined, parameters = {}) {
        if (typeof action == 'object') {
            return this.call(action, true)
        } else if (action == undefined) { //doesn't really work like this
            return this.call(parameters, true)
        } else {
            return this.action(action, parameters, true)
        }
    }
    getData() {
        return Resource.getResource(this.name).data
    }
    action(action, parameters = null, useGET = false,responseType='json') {
        return Resource.action(this.name, action, parameters, useGET,responseType)
    }
    /** deprecated */
    call(parameters, useGET = false) {
        return Resource.action(this.name, null, parameters, useGET)
    }
}
class Resource {
    static loadDependencies(dependencies, server = false) {
        if (!dependencies) {
            return Promise.resolve(true)
        }
        let promises = []
        for (let dependency of dependencies) {
            let ready = true
            let nameToLoad = server ? 'server:' + dependency.name : dependency.name
            if (dependency.type == 'object' || dependency.type == 'dbtable' || dependency.type == 'validator') {
                ready = Resource.registerAndLoad(nameToLoad)
            } else if (dependency.type == 'tag') {
                if (!Tag.isRegistered(dependency.name)) {
                    ready = Tag.registerAndLoad(nameToLoad)
                } else if (!Tag.isReady(dependency.name)) {
                    ready = Resource.loaded(dependency.name)//Tag.ready(dependency.name)
                }
            }
            if (ready !== true) {
                promises.push(ready)
            }
        }
        if (promises.length === 1) {
            return promises[0]
        } else if (promises.length > 1) {
            return Promise.all(promises)
        } else {
            return Promise.resolve(true)
        }
    }
    static evalJavaScript(script, resource) {
        return eval('(' + script + ')');
    }
    static loadedJson(name, json, resolvePromise) {
        Object.assign(Resource.resources[name], json)
        Resource.loadDependencies(json.dependencies, name.startsWith('server:')).then(result => {
            let resource = Resource.resources[name]
            if (resource.implementation) {
                if (resource.implementation.javascript != undefined && resource.implementation.javascript != '') {
                    try {
                        resource.classObj = Resource.evalJavaScript(resource.implementation.javascript, resource)
                        let classNames = Resource.classRegex.exec(resource.implementation.javascript)
                        window[classNames[1]] = resource.classObj
                        if (resource.data) {
                            Object.assign(window[classNames[1]], resource.data)
                        }
                    } catch (error) {
                        let resname = name.startsWith('server:') ? name.substr(7) : name;
                        handleError(resname,error.message,{tab:'javascript', line:error.lineNumber})
                    }
                }
            }
            let waitingResolved = false
            if (resource.classObj != undefined && resource.classObj.initialize != undefined) {

                let initializeResult = resource.classObj.initialize()
                if (typeof initializeResult === 'object') {
                    waitingResolved = true
                    initializeResult.then(initResult => {
                        resource.status = 'ready'
                        Resource.resolveWaiting(resource, resolvePromise)
                    })
                } else {
                    resource.status = 'ready'
                }
            } else {
                resource.status = 'ready'
            }
            if (!waitingResolved) {
                Resource.resolveWaiting(resource, resolvePromise)
            }
            return true
        });
    }
    static appendToAllRequests(obj) {
        Resource.alwaysAppend = obj;
    }
    static addResourcePack(resources) {
        Object.keys(resources).forEach(resourceKey => {
            let resourceName = resources[resourceKey].name
            let resObj = new ResourceObject()
            resObj.name = resourceName
            resObj.status = 'downloading'
            resObj.onload = []
            Resource.resources[resourceName] = resObj
            Tag.tags[resourceName] = { ready: false }
        })
        Object.keys(resources).forEach(resourceKey => {
            let resource = resources[resourceKey]
            Resource.loadedJson(resource.name, resource, undefined)
        })
    }
    static loadPack(resourceNames) {
        return Resource.action('metayota', 'resource_pack', { 'resources': resourceNames }, true).then(resources => {
            Resource.addResourcePack(resources)
        })
    }
    static registerAndLoad(name) {
        if (Resource.resources[name] === undefined) {
            let resObj = new ResourceObject()
            resObj.name = name
            resObj.status = 'downloading'
            resObj.onload = []
            let self = this
            Resource.resources[name] = resObj
            let scriptsPath = Resource.scriptsPath ? Resource.scriptsPath : '/wp-content/plugins/metayota/scripts/'
            let url = Resource.wp !== true ? '/resource/' + name + '.json' : scriptsPath + 'metayota/resource.php?name='+name+'&output=json'
            if (Resource.alwaysAppend != undefined) {
                let queryStr = Resource.objectToQueryString(Resource.alwaysAppend);
                if (url.includes("?")) {
                    url += "&" + queryStr
                } else {
                    url += "?" + queryStr
                }
            }
            return new Promise(function (resolvePromise, reject) {
                let sessionItem = null;
                if (window.cacheEnabled$ === true && false) {
                    sessionStorage.getItem('resource_' + name)
                }
                if (sessionItem) {
                    try {
                        Resource.loadedJson(name, JSON.parse(sessionItem), resolvePromise)
                    } catch (error) {
                        console.error('Could not load tag ' + name, error)
                    }
                } else {

                    fetch(url, { credentials: "include" })
                        .then(result => {
                            
                            if (result.status == 403) {
                                let resname = name.startsWith('server:') ? name.substr(7) : name;
                                handleError(resname,'HTTP 403 - No access to view this resource.', {highlight:'tag'},'access')
                            }
                            return result.text()
                        })
                        .then(result => {
                            if (window.cacheEnabled$ === true && false) {
                                sessionStorage.setItem('resource_' + name, result)
                            }
                            return JSON.parse(result)
                        })
                        .then(json => {
                            Resource.loadedJson(name, json, resolvePromise)
                        }).catch(error => {
                            if (error.error != undefined) {
                                alert(error.error);
                            }
                            console.error("Could not include ", name, error)
                        })
                }
            });
        }
        return Promise.resolve(Resource.resources[name]) // should not return 2 different types
    }

    static resolveWaiting(resource, resolvePromise) {
        let onload = resource.onload
        for (let loadFn of onload) {
            loadFn(resource)
        }
        if (resolvePromise != undefined) {
            resolvePromise(resource)
        }
        return resource
    }
    static callWithJSON(postData, useGET = false) {
        let callURL = ""
        if (Resource.wp === true) {
            let scriptsPath = Resource.scriptsPath ? Resource.scriptsPath : '/wp-content/plugins/metayota/scripts/'
            if (postData.action) {
                callURL = scriptsPath + postData.resource + "/" + postData.action + ".php";
            } else {
                callURL = scriptsPath + postData.resource + "/" + postData.resource + ".php"
            }
        } else {
            if (postData.action) {
                callURL = "/call/" + postData.resource + "/" + postData.action;
            } else {
                callURL = "/call/" + postData.resource
            }
        }

        if (useGET) {
            let sendData = postData.data
            if (Resource.alwaysAppend != undefined) {
                sendData = Object.assign({}, sendData, Resource.alwaysAppend)
            }
            let queryStr = Resource.objectToQueryString(sendData);
            if (queryStr.length > 0) {
                if (callURL.includes("?")) {
                    callURL += "&" + queryStr
                } else {
                    callURL += "?" + queryStr
                }
            }
            return fetch(callURL, {
                method: "GET",
                headers: {
                    'Accept': 'application/json, text/plain, */*'
                },
                credentials: 'include'
            });
        } else {
            let fetchURL = callURL
            if (Resource.alwaysAppend != undefined) {
                if (callURL.includes('?')) {
                    fetchURL += '&' + Resource.objectToQueryString(Resource.alwaysAppend);
                } else {
                    fetchURL += '?' + Resource.objectToQueryString(Resource.alwaysAppend);
                }
            }
            return fetch(fetchURL, {
                method: "POST",
                body: JSON.stringify(postData.data),
                headers: {
                    'Accept': 'application/json, text/plain, */*',
                    'Content-Type': 'application/json'
                },
                credentials: 'include'
            })
        }
    }
    static callText(resource, action, parameters = null) {
        let postData = { "resource": resource, action: action, data: parameters ? parameters : null }
        return Resource.callWithJSON(postData).then(res => res.text()) // todo catch
    }
    static objectToQueryString(obj, prefix = '') {
        const queryString = [];
        for (const key in obj) {
            if (obj.hasOwnProperty(key)) {
                const value = obj[key];
                if (value != undefined) {
                    const encodedKey = encodeURIComponent(key);
                    const prefixedKey = prefix ? `${prefix}[${encodedKey}]` : encodedKey;
                    if (typeof value === 'object' && !Array.isArray(value)) {
                        const nestedQueryString = Resource.objectToQueryString(value, prefixedKey);
                        queryString.push(nestedQueryString);
                    } else if (Array.isArray(value)) {
                        value.forEach(item => {
                            const encodedValue = encodeURIComponent(item);
                            queryString.push(`${prefixedKey}[]=${encodedValue}`);
                        });
                    } else {
                        const encodedValue = encodeURIComponent(value);
                        queryString.push(`${prefixedKey}=${encodedValue}`);
                    }
                }
            }
        }
        return queryString.join('&');
    }
    static action(resource, action, parameters = null, useGET = false,responseContentType='json') {
        let postData = { "resource": resource, action: action, data: parameters ? parameters : null }
        return Resource.callWithJSON(postData, useGET).then((res) => {
            if (res.status == 403) {
                // let resname = resource.startsWith('server:') ? name.substr(7) : name;
                let json = res.json()
                json.then(result=>{
                    let highlight = result.highlight ? result.highlight : 'php';
                    let msg = result.message ? result.message : '403 - No Access - You have no access to make server calls to the resource '+resource;
                    let resourceA = result.resource ? result.resource : resource
                    handleError(resourceA,msg,{highlight},'access');
                }).catch(result=> {
                    handleError(resource,'403 - No Access - You have no access to make server calls to the resource '+resource,{highlight:'php'},'access')
                })
                return json;
            }
            if (res.status == 500) {
                let json = res.json();
                json.then(jsonResult => {
                    handleError(jsonResult.resource,'500 - Error in server call ' + jsonResult.resource + '!',{line:jsonResult.line,tab:jsonResult.tab+'.php'})
                })
                return json;
            }
            if (responseContentType == 'json') {
                return res.json()
            }
            if (responseContentType == 'blob') {
                return res.blob()
            }
            if (responseContentType == 'text') {
                return res.text()
            }
        }).catch((error) => {
            console.error(resource, error)
        });
    }
    static createWithDefaults(name) {
        let resource = Resource.resources[name]
        return Object.assign({}, resource.configuration)        
    }
    static create(name, params = {}) { // At the moment not used
        let resource = Resource.resources[name]
        let cls = resource.classObj
        if (cls === undefined) {
            return params // params
        }
        let newObj = new cls()
        Object.assign(newObj, resource.configuration, params)
        return newObj
    }
    static ready(name) {
        let obj = Resource.resources[name];
        if (obj) {
            if (obj.status == 'downloading') {
                return false
            }
            return obj
        }
        return false;
    }
    static loaded(name) {
        let obj = Resource.resources[name];
        if (obj) {
            if (obj.status == 'downloading') {
                return new Promise(function (resolve) {
                    obj.onload.push(resolve)
                });
            }
            return Promise.resolve(obj);
        } else {
            return Resource.registerAndLoad(name)
        }
    }

    static getResource(name) {
        return Resource.resources[name]
    }
    static cls(name) {
        return Resource.resources[name].classObj
    }
    static call(funcName, parameters) {
        return Resource.action(funcName, null, parameters)
    }
}
Resource.resources = {}
Resource.classRegex = /class ([a-zA-Z]*)/
class Tag {
    constructor() {
        this._subscriptions = new Map();
    }
    get subscriptions() {
        return this._subscriptions;
    }
    set subscriptions(s) {
        this._subscriptions = s
    }
    setup() { }
    init() { }
    ready() { }
    eval(code, element) {
        return eval("(" + code + ")")
    }
    static load(url) {
        if (Tag.loaded[url] === undefined) {
            let loadingInfo = { url: url, promises: [] }
            Tag.loaded[url] = loadingInfo
            var script = document.createElement("script");
            script.type = 'text/javascript'
            script.src = url
            script.onload = function () {
                loadingInfo.loaded = true
                for (let promiseResolveFn of loadingInfo.promises) {
                    promiseResolveFn(true);
                }
            }
            document.body.appendChild(script)
        }
        let loadingInfo = Tag.loaded[url];
        if (loadingInfo.loaded == true) {
            return Promise.resolve(true)
        }

        let loadPromise = new Promise(function (resolve) {
            loadingInfo.promises.push(resolve)
        });

        return loadPromise
    }
    get tagName() {
        return this.tagModel.tagName;
    }
    call(funcName, parameters) {
        return Tag.call(funcName, parameters)
    }
    callJson(resource, parameters) {
        return Resource.action(resource, null, parameters)
    }
    static callJson(resource, parameters) {
        return Resource.action(resource, null, parameters)
    }
    static call(funcName, parameters) {
        return Tag.callJson(funcName, parameters);
    }
    fire(eventname, event = {}) {
        if (this.tagModel.events !== undefined) {
            let expression = this.tagModel.events[eventname];
            if (expression !== undefined) {
                //expression.call(this, this.parentTag, event);
                expression.callWithTagAndElementAndEvent(this.parentTag, this, event)
            }
            let expression2 = this.tagModel.events['event'];
            if (expression2 !== undefined) {
                if (this.parentTag) {
                    expression2.callWithTagAndElementAndEvent(this.parentTag, this, { eventname, event })
                }
            }
        }

    }
    static getModelElement(tagname) {
        let modelElement = Tag.tags[tagname].modelElement;
        return modelElement === undefined ? null : modelElement;
    }


    static parseModelTag(resource, tagData, tagname) { // TODO: DIRECTLY FETCH JSON INSTEAD OF TEXT 
        let implementation = resource.implementation
        if (implementation) {
            if (resource.configuration) {
                tagData.configuration = resource.configuration
            }
            if (implementation.javascript != undefined && implementation.javascript != '') {
                try {
                    tagData.classObj = resource.classObj
                    // tagData.classObj = eval('(' + implementation.javascript + ')');
                    tagData.classObj.bind(resource)
                    let classNames = Resource.classRegex.exec(implementation.javascript)
                    window[classNames[1]] = tagData.classObj
                } catch (error) {
                    console.error('Tag "' + tagname + '" (implementation.javascript) could not be parsed:', tagData, error)
                    // link to bug, ?editor=resource=tagname&tab=implementation/javacript
                }
            }
            if (implementation.html !== undefined && implementation.html != '') {
                let domparser = new DOMParser();
                let tagEl = document.createElement('template');
                tagEl.innerHTML = implementation.html;

                if (tagEl.content.childNodes.length > 1) {
                    let wrapper = document.createElement('div');
                    while (tagEl.content.firstChild) {
                        wrapper.appendChild(tagEl.content.firstChild);
                    }
                    tagEl.content.appendChild(wrapper);
                }

                let modelElement = new ModelElement();
                let modelBrowserElement = (tagEl.content.childNodes.length > 1) ? tagEl.content : tagEl.content.firstElementChild
                modelElement.parse(modelBrowserElement, {});
                Tag.tags[tagname].modelElement = modelElement;
            }
            if (implementation.css !== undefined && implementation.css != '') {
                var css = document.createElement("style");
                css.type = "text/css";
                css.innerHTML = implementation.css;
                document.head.appendChild(css);
            }
            if (tagData.classObj === undefined) {
                tagData.classObj = Tag
            }
        }
        tagData.ready = true;

        return true;
    }
    static ready(tagname) {
        if (Tag.tags[tagname] == undefined) {
            Tag.tags[tagname] = { ready: false }
        }
        return Resource.loaded(tagname).then(resource => {
            if (!Tag.tags[tagname].ready) {
                return Tag.parseModelTag(resource, Tag.tags[tagname], tagname)
            } else {
                return true
            }
        });
    }
    static getDefaults(tagname) {
        let tag = Tag.tags[tagname];
        if (tag === undefined) {
            return {}
        }
        return tag.configuration
    }
    static getClass(tagname) {
        let tag = Tag.tags[tagname];
        if (tag === undefined) {
            // console.warn('Tag ' + tagname + ' is not registered');
            return null;
        }
        if (tag.classObj === undefined) {
            return null;
        }

        return tag.classObj;
    }
    static isReady(tagname) {
        if (Tag.tags[tagname] === undefined) {
            return false
        }
        return (Tag.tags[tagname].ready === true);
    }
    static isRegistered(tagname) {
        if (tagname == undefined) {
            return false;
        }
        return Tag.tags[tagname] !== undefined;
    }
    static render(tagname, attributes = {}) {
        return TagModel.renderTag(tagname, attributes, null)
    }
    static clear(tagname) {
        Tag.tags[tagname] = undefined
        Resource.resources[tagname] = undefined
        sessionStorage.removeItem('resource_' + tagname)
    }
    static register(tagname) {
        if (!Tag.tags[tagname]) {
            let url = '/resource-manager/resource.php?name=' + tagname
            Tag.tags[tagname] = { url: url, ready: false }
        }
    }
    static registerAndLoad(tagname) {
        Tag.tags[tagname] = { ready: false }
        return Resource.loaded(tagname).then(resource => {
            if (!Tag.tags[tagname].ready) {
                Tag.tags[tagname] = resource
            }
            return resource;
        })
    }
    static registerPreloaded(tagname, fetchPromise) {
        Tag.tags[tagname] = { fetchPromise: fetchPromise, ready: false };
    }
    static registerFrameworkTag(tagname, classObj) {
        Tag.tags[tagname] = { classObj: classObj, ready: true };
    }
    publish(varname, object) {
        Tag.publish(varname, object)
    }
    static publish(varname, object) {
        window[varname] = object;
        notify(varname)
    }
    changed(property) {
        // this.update('property');
    }
    render(browserElement) { }
    subscribe(name, attribute, browserElement) {
        let subscriptions = this.subscriptions.get(name)
        if (!subscriptions) {
            subscriptions = []
            this.subscriptions.set(name, subscriptions)
        }
        subscriptions.push({ attribute: attribute, browserElement: browserElement })
        if (!browserElement.tagSubscriptions) {
            browserElement.tagSubscriptions = []
        }
        browserElement.tagSubscriptions.push({ name: name, tag: this });
    }
    unsubscribe(name, browserElement) {
        if (this.subscriptions) {
            let subscriptions = this.subscriptions.get(name)
            for (let i = subscriptions.length - 1; i >= 0; i--) {
                let subscription = subscriptions[i];
                if (subscription.browserElement == browserElement) {
                    subscriptions.splice(i, 1);
                }
            }
        }
    }
    subscribeTagAttribute(name, tagAttribute, tag, expression) {
        let subscriptions = this.subscriptions.get(name)
        if (!subscriptions) {
            subscriptions = []
            this.subscriptions.set(name, subscriptions)
        }
        subscriptions.push({ tagAttribute: tagAttribute, tag: tag, expression: expression });
    }
    unsubscribeTagAttribute(name, tag) {
        let subscriptions = this.subscriptions.get(name)
        if (subscriptions) {
            for (let subscription of subscriptions) {
                if (subscription.tag == tag) {
                    subscription.remove = true;
                }
            }
        }
    }

    updateItem(name, index) {
        this.update(name + '[' + index + ']')
    }
    updateAppended(name) {
        this.update(name + '.length')
    }
    setAttribute(name, value) {
        this[name] = value
        this.update('this.' + name)
    }
    update(name) { // Todo: use a local subscriptions object here?
        name = name.replace(/this/g, 'tag'); //bug, tag name will be replaced regex
        if (name.startsWith('tag.')) {
            if (!this.subscriptions) {
                return
            }
            let subscriptionKeys = this.subscriptions.keys()
            for (let subscriptionName of subscriptionKeys) {
                if (name.startsWith(subscriptionName + '.') || subscriptionName.startsWith(name) || name == subscriptionName) { //?

                    let subscriptions = this.subscriptions.get(subscriptionName);
                    for (let subscriptionPath in subscriptions) {
                        let subscription = subscriptions[subscriptionPath];
                        if (subscription.remove !== true) {
                            if (subscription.attribute !== undefined) {
                                subscription.attribute.renderAttribute(subscription.browserElement, { tag: this, forIdx: subscription.browserElement.forIdx }); //
                            } else {
                                let tag = subscription.tag;
                                if (tag.destroyed === undefined) {
                                    let result = subscription.expression.callWithTagAndElement(tag.parentTag, tag)
                                    let code = 'tag.' + subscription.tagAttribute + ' = result'
                                    eval(code); // todo: use compiled fn
                                    tag.update('tag.' + subscription.tagAttribute); //
                                }
                            }
                        }
                    }
                }
            }
        } else {
            notify(name);
        }
    }
    destroy() {
        this.tagModel.unsubscribeForParameterChanges(this)
        this.subscriptions = undefined
        this.destroyed = true
    }
}
Tag.tags = {};
Tag.loaded = []
//make static method
var matchAll = function (string) {
    const regex = /(([0-9a-zA-Z_$\.]|\[[0-9]{1,}\]|\[\'.*?\'\]|\[.*?\.forIdx.*?\]\]){2,})/g;
    const str = string;
    let m;
    var matches = [];
    while ((m = regex.exec(str)) !== null) {
        if (m.index === regex.lastIndex) {
            regex.lastIndex++;
        }
        m.forEach((match, groupIndex) => {
            if (groupIndex == 1) {
                matches.push(match);
            }
        });
    }
    //filter unique
    return matches;
};
class Expression {

    constructor(expression, options) {
        this.originalExpression = expression
        this.expression = expression;
        this.subscriptions = [];
        this.tagSubscriptions = [];
        this.parseExpression(options);
    }
    parseExpression(options) {
        this.expression = this.preprocessExpression(options)
        let code;
        let definitions = ''
        if (options.aliases) {
            for (let alias of options.aliases) {
                definitions += 'let ' + alias.varname + " = " + alias.expression + "\n"
            }
        }
        if (options.tagAndvalue) {
            code = '(function(tag,value) { ' + definitions + ' ' + this.expression + ' })';
        } else if (options.returnVoid) {
            code = '(function(element,node,tag,event) { ' + definitions + ' ' + this.expression + ' })';
        } else {
            code = '(function(element,node,tag,event) { ' + definitions + ' return (' + this.expression + ') })';
        }
        try {
            this.fn = eval(code);
        } catch (error) {
            console.error('Expresssion: parseExpression failed', code, error)
            this.fn = eval('(function() {return false})')
        }
    }
    parseSubscriptions(expression) {
        let expressionParts = matchAll(expression);
        for (let expressionPart of expressionParts) {
            if (expressionPart.startsWith('tag.') || expressionPart.startsWith('this.')) {
                this.tagSubscriptions.push(expressionPart.replace('this.','tag.'));
            } else if (expressionPart.indexOf('$') != -1) {
                this.subscriptions.push(expressionPart);
            } /*else {
                this.subscriptions.push(expressionPart) // maybe recoginize other selectors like {myvar.subvar}
            } NOT SURE IF THIS IS REQUIRED*/
        }
    }
    getTagSubscriptions(options) {
        if (options.forIdx) { //check text for forIdx
            let tagSubscriptions = [];
            for (let subscription of this.tagSubscriptions) {
                let newTagSubscription = subscription;
                for (let forIdxName in options.forIdx) {
                    let search = 'element.forIdx[\'' + forIdxName + '\']';
                    newTagSubscription = newTagSubscription.split(search).join(options.forIdx[forIdxName]);
                }
                tagSubscriptions.push(newTagSubscription) //regex
            }
            return tagSubscriptions;
        } else {
            return this.tagSubscriptions;
        }
    }
    preprocessExpression(options) {
        let expression = this.expression;
        if (options.jscontext === true) {
            expression = Expression.processJscontext(expression, options)
            this.parseSubscriptions(expression)
        } else if (expression.includes('{') || (options.textcontext === true)) {
            expression = Expression.convertToJavaScript(expression, options, this);
        } else {
            expression = Expression.processJscontext(expression, options) // ?
            this.parseSubscriptions(expression)
        }
        return expression;
    }
    static processJscontext(expr, options) {
        if (!expr.startsWith('{') && !expr.startsWith('[')) {//?

            expr = expr.replace(/ and /g, ' && ');
            expr = expr.replace(/ or /g, ' || ');
            expr = expr.replace(/ then /g, ' && ');
        }
        expr = expr.replace(/this/g, 'tag'); // SHOULD ACTUALLY ONLY REPLACE AT BEGINNING OF IDENTIFIERS 
        return expr;
    }
    static convertToJavaScript(expression, options, addSubscriptions = null) {
        let js = expression.replace(/(\s*[\n\r]+\s*)+/g, '')
        let processedJs = '';

        let jsContext = 0;
        let jscontextstr = '';
        let lastchar = '';
        for (let i = 0; i < js.length; i++) {
            let char = js[i];
            /*if (jsContext === 0 && ((char === ' ' && lastchar === ' ') || char === '\n' || char === '\t' || char === '\r')) {
                lastchar = ' '
                continue
            }*/
            if (jsContext === 0) lastchar = char;
            if (char === '{') {
                jsContext++;
                if (jsContext === 1) {
                    jscontextstr = '';
                    if (i > 0) {
                        processedJs += '\' + (';
                    } else {
                        processedJs += '('
                    }
                } else {
                    if (jsContext > 1)
                        jscontextstr += char
                    else
                        processedJs += char;
                }
            } else if (char === '}') {
                jsContext--;
                if (jsContext === 0) {
                    let processedJSContext = Expression.processJscontext(jscontextstr, options)
                    processedJs += processedJSContext
                    if (addSubscriptions !== null) {
                        addSubscriptions.parseSubscriptions(processedJSContext)
                    }
                    jscontextstr = ''
                    processedJs += ')'
                    if (i < (js.length - 1)) {
                        processedJs += ' + \'';
                    }
                } else {
                    jscontextstr += char;
                }
            } else {
                if (jsContext > 0) {
                    if (i === 0) {
                        jscontextstr += '\'';
                    }
                    jscontextstr += char;
                    if (i === js.length - 1) {
                        jscontextstr += '\'';
                    }
                } else {
                    if (i === 0) {
                        processedJs += '\'';
                    }
                    if (char == '\'') {
                        processedJs += '\\'
                    }
                    processedJs += char;
                    if (i === js.length - 1) {
                        processedJs += '\'';
                    }
                }
            }
        }
        processedJs += jscontextstr;
        return processedJs;
    }
    callWithElement(element, options) {
        try {
            return this.fn(element, element.node, element.tag, null);
        } catch (error) {
            let resname = element.tag.tagModel.tagName;
            let codepoint = this.originalExpression.replace("tag.", "this.").trim()
            handleError(resname,error.message,{tab:'html', codepoint:codepoint})
            return false
        }
    }
    callWithTag(tag) {
        return this.fn(null, null, tag, null);
    }
    callWithTagAndValue(tag, value) {
        return this.fn(tag, value)
    }
    callWithTagAndElement(tag, element) {
        try {
            return this.fn(element, null, tag, null);
        } catch (error) {

            console.error((tag ? tag.tagModel.tagName : '') + 'callWithTagAndElement - error', 'expression', this.originalExpression, error, 'tag:', tag, 'element:', element, this)
            return false
        }
    }
    callWithTagAndElementAndEvent(tag, element, event) {
        try {
            return this.fn(element, null, tag, event);
        } catch (error) {

            let resname = tag.tagModel.tagName;
            let codepoint = this.originalExpression.replace("tag.", "this.").trim()
            handleError(resname,error.message, {tab:'html',codepoint:codepoint})
            return true
        }
    }
    call(element, tag, event) {
        try {
            return this.fn(element, element.node, tag, event);
        } catch (error) {
            handleError(tag.name,error.message, {tab:'html',codepoint:this.originalExpression.replace("tag","this")})
            return false
        }
    }
}
class Attribute {
    constructor(name, value, options) {
        this.name = name;
        this.value = value;
        this.expression = null;
        this.parse(options);
    }
    create(name, value) {
        return null;
    }
    subscribe(browserElement, options) {
        if (this.expression) {
            for (let subscription of this.expression.subscriptions) {
                browserElement.bind(this, subscription);
            }
            let tagSubscriptions = this.expression.getTagSubscriptions(options);
            for (let subscription of tagSubscriptions) {
                if (browserElement.tag) {
                    browserElement.tag.subscribe(subscription, this, browserElement);
                }
            }
        }
    }
    unsubscribe(browserElement) { }
    static parseAttribute(modelNode, name, value, options) {
        var attribute = Attribute.createAttribute(name, value, options);
        if (attribute !== null) {
            modelNode.removeAttribute(name);
            return attribute;
        }
        if (this.containsExpressions(value)) {
            return new ExpressionAttribute(name, value, options);
        }
        return null;
    }
    static registerAttribute(name, create) {
        Attribute.attributeTypes[name] = create;
        Attribute.attributeTypeNames.push(name);
    }
    static isRegistered(name) {
        return Attribute.attributeTypes[name] !== undefined;
    }
    static createAttribute(name, value, options) {
        var attrType = Attribute.attributeTypes[name];
        if (attrType !== undefined) {
            return new attrType(name, value, options);
        }
        if (name.substr(0, 1) == '(') {
            return new EventAttribute(name, value, options);
        }
        return null;
    }
    static containsExpressions(value) {
        return (value.indexOf('{') !== -1);
    }
    renderAttribute(browserElement, options) {
        if (browserElement.node) {
            let value;
            if (this.expression !== null) {
                value = this.expression.callWithElement(browserElement, options);
            } else {
                value = this.value
            }
            if (value === false || value === undefined) {
                value = ''
            }
            this.render(browserElement.node, value, browserElement)
        }
    }
    parse(options) {
        let newOptions = Object.assign({}, options, { textcontext: true })
        this.expression = new Expression(this.value, newOptions);
    }
    destroy() { }
}
Attribute.browserAttribute = false;
Attribute.attributeTypeNames = [];
Attribute.attributeTypes = {};
class ExpressionAttribute extends Attribute {
    render(node, value) {
        if (node) {

            if (node.setAttribute) {
                if (this.name == 'value') {
                    node.value = value
                } else {
                    node.setAttribute(this.name, value);
                }
            }

            if (!value) {
                if (node.removeAttribute != undefined) {
                    node.removeAttribute(this.name);
                }
            }
        }
    }
}
class EventAttribute extends Attribute {
    parse(options) {
        this.eventName = this.name.substr(1, this.name.length - 2);
        let newOptions = Object.assign({}, options, { returnVoid: true })
        let definitions = ''
        if (options.aliases) {
            for (let alias of options.aliases) {
                definitions += 'let ' + alias.varname + " = " + alias.expression + "\n"
            }
        }
        this.code = '(function(element,node,tag,event) { ' + definitions + Expression.processJscontext(this.value, newOptions) + ' } )';
    }
    subscribe(element) {
        //should save event on element...to destroy when browser element is destroyed?
        let onEvent = function (event) {
            try {
                if (this.eventAttribute.fn == undefined) {
                    this.eventAttribute.fn = eval(this.eventAttribute.code);
                }
                this.eventAttribute.fn(this.element, this.element.node, this.element.tag, event)
            } catch (error) {
                let resname = this.element.tag.tagName;
                let codepoint = this.eventAttribute.name + '="' + this.eventAttribute.value + '"';
                handleError(resname,error.message, {tab:'html',codepoint:codepoint})
            }
        }.bind({ element: element, eventAttribute: this });

        element.node.addEventListener(this.eventName, onEvent);
    }
    unsubscribe(browserElement) {
        browserElement.node.removeEventListener(this.eventName, this.onevent);
    }
    destroy() {
        this.expression = null;
    }
    renderAttribute(browserElement) { }
}
class ModelAttribute extends Attribute {
    parse(options) {
        let eventCode = this.value + ' = node.value;this.update(\'' + this.value + '\');'
        this.eventAttribute = new EventAttribute('(change)', eventCode, options)
        this.expressionAttribute = new ExpressionAttribute('value', '{' + this.value + '}', options)
    }
    subscribe(element) {
        this.eventAttribute.subscribe(element)
    }
    renderAttribute(browserElement, options) {
        this.expressionAttribute.renderAttribute(browserElement, options)
    }
    unsubscribe(browserElement) {
        this.eventAttribute.unsubscribe(browserElement)
    }
    destroy() {
        this.eventAttribute.destroy()
        this.expressionAttribute.destroy()
    }
}
Attribute.registerAttribute('model', ModelAttribute);
class TextNodeAttribute extends Attribute {
    render(node, value) {
        node.nodeValue = value;
    }
}
class TextareaNodeAttribute extends Attribute {
    render(node, value) {
        node.value = value;
    }
}
class ForAttribute extends Attribute {
    parse(options) {
        let arr = this.value.split(' of ');
        let variable = arr[0];
        this.collection = arr[1].replace(/this/g, 'tag');
        this.expression = new Expression(this.collection, options);
        if (this.expression.subscriptions.length == 1) {
            this.expression.subscriptions[0] += '.length';
        }
        if (this.expression.tagSubscriptions.length == 1) {
            this.expression.tagSubscriptions[0] += '.length';
        }
        if (options.aliases === undefined) {
            options.aliases = [];
        }
        let idxexpr = 'element.forIdx[\'' + this.collection + '\']'
        let expression = this.collection + '[' + variable + '_index]'
        options.currentForIndex = variable + '_index'
        options.currentCollection = this.collection
        options.aliases.push({ varname: options.currentForIndex, expression: idxexpr, replaceForSubscription: true });
        options.aliases.push({ varname: variable, expression: expression });
    }

    renderAttribute(browserElement, options) {

        options.ignoreThis = true
        let collection = null;
        collection = this.expression.callWithElement(browserElement, options);

        if (!collection) {
            collection = []
        }
        let newBrowserEl = null;
        browserElement.destroySubitems()
        browserElement.children = []
        let lastEl = 0

        if (collection && browserElement.node.nodeType !== 8) {
            if (lastEl === 0) {
                browserElement.node.innerHTML = "";
            }
            for (let i = lastEl; i < collection.length; i++) {
                let cOptions = Object.assign({}, options)
                cOptions.renderChildren = true
                if (cOptions.forIdx) {
                    cOptions.forIdx = Object.assign({}, cOptions.forIdx, {
                        [this.collection]: i
                    })
                } else {
                    cOptions.forIdx = {
                        [this.collection]: i
                    }
                }
                let childBrowserEl = browserElement.modelElement._renderChildren(cOptions, null);
                browserElement.children.push(childBrowserEl);
                let childNodes = Array.from(childBrowserEl.node.childNodes);
                childBrowserEl.childNodes = [];
                for (let node of childNodes) {
                    browserElement.node.appendChild(node);
                    childBrowserEl.childNodes.push(node)
                }

            }
        }
        options.renderChildren = false;

        options.ignoreThis = false
    }
}
Attribute.registerAttribute('for', ForAttribute);
class IfAttribute extends Attribute {
    renderAttribute(browserElement, options) {
        if (this.ignoreThis === true) {
            return; // RETURN MIGHT CAUSE PROBLEMS
        }
        this.ignoreThis = true;
        
        // Preserve the parent's forIdx context
        const parentForIdx = browserElement.forIdx;
        
        options.render = this.getRender(browserElement);
        if (options.render === true) {
            if (browserElement.node === null || browserElement.node.nodeType === 8) {
                // Pass through the forIdx context when rendering
                const newOptions = {...options, forIdx: parentForIdx};
                browserElement.render(newOptions);
            }
        } else {
            browserElement.unplace('if (' + this.expression.expression + ')');
        }
        this.ignoreThis = false;
    }

    getRender(browserElement) {
        return !!this.expression.callWithElement(browserElement);
    }

    parse(options) {
        let newOptions = Object.assign({}, options, { jscontext: true })
        this.expression = new Expression(this.value, newOptions);
    }
}
Attribute.registerAttribute('if', IfAttribute);
class LastAttribute extends IfAttribute {
    parse(options) {
        this.value = '(' + options.currentCollection + '.length - 1) === ' + options.currentForIndex
        super.parse(options)
    }
}
Attribute.registerAttribute('last', LastAttribute);
class FirstAttribute extends IfAttribute {
    parse(options) {
        this.value = '0 === ' + options.currentForIndex
        super.parse(options)
    }
}
Attribute.registerAttribute('first', FirstAttribute);
class ElementAttribute extends Attribute {
    renderAttribute(browserElement) {
        browserElement.tag[this.value] = browserElement.node;
    }
}
Attribute.registerAttribute('element', ElementAttribute);
class LinkAttribute extends Attribute {
    subscribe(browserElement, options) {
        let onEvent = function (event) {
            if (browserElement.link.startsWith('/'))
                router$.goto(browserElement.link);
            else
                router$.go(browserElement.link);
            event.preventDefault();
            event.stopPropagation();
        }.bind(this);

        browserElement.node.addEventListener('click', onEvent);
        return super.subscribe(browserElement, options)
    }
    render(node, value, browserElement) {
        browserElement.link = value;
        node.setAttribute('target', '_self')
        node.setAttribute('href', value)
    }
}
Attribute.registerAttribute('link', LinkAttribute);
class IncludeTag extends Tag {
    render(browserElement) {
        if (browserElement == null) {
            browserElement = new BrowserElement(document.createElement('div'))
            this.browserElement = browserElement
        }
        if (this._tag) {
            Resource.loaded(this._tag).then(resource => {
                this.renderedTag = TagModel.renderTag(this._tag, this.attributes, null, { tag: this.parentTag }, this.tagModel.events)
                browserElement.node.innerHTML = ''
                if (this.renderedTag) {
                    if (browserElement.node.nodeType == 8) {
                        browserElement.node.replaceWith(this.renderedTag.node)
                        browserElement.node = this.renderedTag.node
                    } else {
                        browserElement.node.appendChild(this.renderedTag.node)
                    }
                }
                if (this.renderedTag.tag) {
                    Object.assign(this.renderedTag.tag, this.attributes)
                }
                this.fire('loaded', resource)

            });
        }
        return browserElement
    }

    set tag(tag) {
        if (this._tag == tag) {
            return
        }
        if (this._tag != undefined && this._tag != tag) {
            if (this.renderedTag != undefined) {
                this.renderedTag.destroy()
            }
        }
        if (!tag && this._tag) {
            if (this.browserElement) {
                this.browserElement.destroySubitems()
                this.browserElement.node.innerHTML = ''
                this._tag = undefined
                return
            }
        }
        if (!tag || tag.toLowerCase() == this._tag) {
            return;
        }
        this._tag = tag.toLowerCase();
        if (this.browserElement) {
            this.render(this.browserElement);
            /*if (this.renderedTag) {
                Object.assign(this.renderedTag.tag, this.attributes)
            }*/
        }
    }
    get tag() {
        return this._tag;
    }
    set attributes(a) {
        this._attributes = a
        if (this.renderedTag && this.renderedTag.tag) {
            Object.assign(this.renderedTag.tag, a)
        }
    }
    get attributes() {
        return this._attributes
    }
}
Tag.registerFrameworkTag('include', IncludeTag)
window.translate = function (e) { return e; }
window.handleError = function(resourceName,message, hash={}, editorView='vscode') {
    if (resourceName+'.php' == hash.tab) {
        hash.tab = 'php'
    }
    hash.message = message
    hash = Object.keys(hash)
        .map(key => `${key}=${encodeURIComponent(hash[key])}`)
        .join(',');

    hash = `#${hash}`;
    
    let errorLink;
    if (Resource.wp) {
        errorLink =  '/editor/resource/' + resourceName + '/'+editorView+hash
    } else {
        errorLink = 'https://static.metayota.com/editor/resource/' + resourceName + '/'+editorView+hash
    }
    let toast = { 'type': 'toast', 'message': 'Resource ' + resourceName + ': ' + message, sticky: true, 'button_name': 'Editor', 'link': errorLink}
    if (window.toast$) {
        toast$.display(toast)
    } else {
        if (!window.toasts) {
            window.toasts = []
        }
        window.toasts.push(toast)
        console.log("Resource '" + resourceName + "' produced an error: " + message);
    }
    window.parent.postMessage(toast , location.origin)
    //console.error((resourceName) + ' - Error in Expression: "' + hash.codepoint + '" ')
}
function initializeFromHTML() {
    const allElements = document.getElementsByTagName('*');
    const elementsToProcess = [];
    
    // Collect registered tags
    for (let i = 0; i < allElements.length; i++) {
        const element = allElements[i];
        const tagName = element.tagName.toLowerCase();
        
        if (Tag.isRegistered(tagName)) {
            elementsToProcess.push(element);
        }
    }
    
    // Process each tag
    for (let element of elementsToProcess) {
        const tagName = element.tagName.toLowerCase();
        
        // Create and parse model element to handle expressions
        const modelElement = new ModelElement();
        modelElement.parse(element, {});
        
        Tag.ready(tagName).then(result => {
            let renderedTag = modelElement.render({ tag: null });
            
            // Replace the original element
            if (renderedTag && renderedTag.node) {
                element.replaceWith(renderedTag.node);
            }
        });
    }
}