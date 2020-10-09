<?php

namespace App\Models;

// "Model" class for wrapping location data from used APIs
class Location {

    private $locationName;
    private $locationNameJA;
    private $wiki;
    private $population;
    private $latitude;
    private $longitude;

    function __construct($geoData, $latLng) {
        $regionName = explode(",", $geoData['region']);
        (isset($geoData['region']) && isset($geoData['osmtags']['name_en'])) ? $this->locationName = $regionName[1].", ".$geoData['osmtags']['name_en'].", ".$regionName[0] : "";
		(isset($geoData['region']) && isset($geoData['osmtags']['name_ja'])) ? $this->locationNameJA = $geoData['osmtags']['name_ja'] : "";
		isset($geoData['osmtags']['wikipedia']) ? $this->wiki = $geoData['osmtags']['wikipedia'] : "";
		(isset($geoData['region']) && isset($geoData['osmtags']['population'])) ? $this->population = $geoData['osmtags']['population'] : "";
		isset($latLng["lat"]) ? $this->latitude = $latLng["lat"] : "";
        isset($latLng["lng"]) ? $this->longitude = $latLng["lng"] : "";
    }

    public function getLocationName(){
        return $this->locationName;
    }
    public function getLocationNameJA(){
        return $this->locationNameJA;
    }
    public function getWiki(){
        return $this->wiki;
    }
    public function getPopulation(){
        return $this->population;
    }
    public function getLatitude(){
        return $this->latitude;
    }
    public function getLongitude(){
        return $this->longitude;
    }


    public function toJSONArray() {
        return array(
            'locationName' => $this->getLocationName(),
            'locationNameJA' => $this->getLocationNameJA(),
            'wiki' => $this->getWiki(),
            'population' => $this->getPopulation(),
            'longitude' => $this->getLongitude(),
            'latitude' => $this->getLatitude(),
        );
    }
}

?>