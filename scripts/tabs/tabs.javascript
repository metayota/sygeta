class Tabs extends Tag {

	set tabs(t) {
		if (t && t.length > 0) {
			if ((typeof t[0]) == 'string') {
				let tabs = []
				for (let variable of t) {
					tabs.push({name:variable,value:variable})
				}
				t = tabs
			}
		}
		this._tabs = t
		this.update('this.tabs')
	}

	get tabs() {
		return this._tabs
	}

    set active(activeTab) {
        this._activeTab = activeTab;
        this.update('this.active');
    }

    get active() {
        return this._activeTab;
    }

    activateTab(node, tab) {
        this.active = tab.name;
        this.update('this.active')
        this.fire('change', tab.value) // ? tab.value : tab.name
    }
}