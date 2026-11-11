<?php namespace Metayota; include_once('../metayota/library.php');  ?><div class="property-listing">
  <img src="{this.image_url}" alt="{this.property_type} in {this.location}">
  <div class="details">
    <h2>{this.property_type} in {this.location}</h2>
    <p class="price">{this.price}</p>
    <p>{this.bedrooms} Bedrooms | {this.bathrooms} Bathrooms | {this.area} sqft</p>
    <p>{this.description}</p>
    <h3>Amenities:</h3>
    <ul>
      <li for="amenity of this.amenities">{amenity}</li>
    </ul>
    <p>Listing Type: {this.listing_type}</p>
    <p>Contact: {this.contact_info}</p>
    <a href="{this.listing_url}">View Full Listing</a>
  </div>
</div>