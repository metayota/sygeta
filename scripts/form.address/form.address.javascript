class AddressInput extends Tag {
    
    init() {
        this.waitForMaps = Tag.load('https://maps.googleapis.com/maps/api/js?key='+resource.getData().api_key+'&libraries=places&language='+translate('language_code')+'&region='+translate('region_code'))
    }

    render() {
        this.waitForMaps.then(result=> {
            var autocomplete = new google.maps.places.Autocomplete(
                (this.autocomplete),
                { types: ['geocode'] }
            );
            autocomplete.addListener('place_changed',  () => {
                var place = autocomplete.getPlace();
                // How to get the lat: place.geometry.location.lat()
                // place.geometry.location.lng()
                this.fire('change',place.formatted_address)
                this.setAttribute('value',place.formatted_address)
            });
        })
    }

    resourceChanged(value) {
        if (this.readonly === true) return
	    this._value = value
	    this.fire('change', value)
    }
}