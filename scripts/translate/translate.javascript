class Translation extends Tag {
    static initialize() {
        window.translate = function (k, parameters = {}) {
            if (Translation.translations == undefined) {
                return k
            }
            let translation = Translation.translations[k]
            if (translation == undefined) {
                translation = k;
            } else {
                if (typeof translation === 'object') {

                    let additionalGeneratedKeys = [];
                    for(let dependsOn of translation.depends_on) {
                        additionalGeneratedKeys.push(parameters[dependsOn]);
                    }
                    let realKey = k;
                    if (additionalGeneratedKeys.length > 0) {
                        realKey += '_' + additionalGeneratedKeys.join('_');
                     }
                    if (Translation.translations[realKey] !== undefined) {
                        return translate(realKey, parameters)
                    } else {
                        translation = translation.translation
                    }
                }
                translation = translation.replace(/\[\[(\w+)\]\]/g, function(_, key) {
                    return translate(key,parameters);
                });
                translation = translation.replace(/\[(\w+)\]/g, function(_, key) {
                    if (typeof parameters != 'object') {
                        return translate(parameters);
                    } else {
                        if (parameters[key] !== undefined) {
                            return translate(parameters[key]);
                        } else {
                            return translate(key);
                        }
                    }
                });
                
                translation = translation.replace(/\{(\w+)\}/g, function(_, key) {
                    if (typeof parameters != 'object') {
                        return parameters;
                    } else {
                        if (parameters[key] !== undefined) {
                            return parameters[key];
                        } else {
                            return key;
                        }
                    }
                });
            }
            return translation;
        }
      /*  let usergroupId = window.loggedInUser$ != undefined ? loggedInUser$.usergroup_id : 0;
        if (usergroupId == 3) { // is ADMIN
            resource.action('load-translation').then(result=> {
                Translation.translations = result
                        
            })
        }*/
        Translation.translations = resource.getData();
    }
}