<?php

namespace App\Models;

use function _\get;

// "Model" class for wrapping location data from used APIs
class Location  {

    private $locationName;
    private $latitude;
    private $longitude;

    function __construct($geoData) {
       
        $this->locationName = $this->parseGoogleLocationName(get($geoData, "results[0].address_components"));
		$this->latitude = get($geoData, "results[0].geometry.location.lat");
        $this->longitude = get($geoData, "results[0].geometry.location.lng");
      
    }

    public function getLocationName(){
        return $this->locationName;
    }
    public function getLatitude(){
        return $this->latitude;
    }
    public function getLongitude(){
        return $this->longitude;
    }

    public function toArray() {
        return [
            'locationName' => $this->getLocationName(),
            'longitude' => $this->getLongitude(),
            'latitude' => $this->getLatitude()
        ];
    }

    private function parseGoogleLocationName($name_parts){
        $location_name = array();
        $name_parts = array_slice($name_parts, 1, -1);
        foreach ($name_parts as $name_part) {
            array_unshift($location_name, $name_part["long_name"]);
        }
        return join(", ", $location_name);
    }
}

?>