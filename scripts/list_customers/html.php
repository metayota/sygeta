<?php namespace Metayota; include_once('../metayota/library.php');  ?><div class="property-list">
  <div class="property-card" for="property of this.properties">
    <img src="{property.images}" alt="{property.address}">
    <div class="property-details">
      <h2>{property.address}</h2>
      <p>{property.city}, {property.state} {property.zip_code}</p>
      <p>${property.price}</p>
      <p>{property.bedrooms} BR | {property.bathrooms} BA | {property.square_footage} sqft</p>
      <p>{property.description}</p>
    </div>
  </div>
</div>