<?php

namespace App\Models;

use function _\get;

// "Model" class for wrapping location data from used APIs
class Location  {

    private $locationName;
    private $locationNameJA;
    private $wiki;
    private $population;
    private $latitude;
    private $longitude;

    /*
    function __construct($geoData, $latLng) {
        $regionName = explode(",", $geoData['region']);
        if (isset($geoData['region'])) {
            isset($geoData['osmtags']['name_en']) ? $this->locationName = $regionName[1].", ".$geoData['osmtags']['name_en'].", ".$regionName[0] : "";
		    isset($geoData['osmtags']['name_ja']) ? $this->locationNameJA = $geoData['osmtags']['name_ja'] : "";
            isset($geoData['osmtags']['population']) ? $this->population = $geoData['osmtags']['population'] : "";
        }
        isset($geoData['osmtags']['wikipedia']) ? $this->wiki = $geoData['osmtags']['wikipedia'] : "";
		isset($latLng["lat"]) ? $this->latitude = $latLng["lat"] : "";
        isset($latLng["lng"]) ? $this->longitude = $latLng["lng"] : "";
    }*/
    /*
    function __construct($geoData, $latLng, $schema) {
        $out = new \Symfony\Component\Console\Output\ConsoleOutput();
		$out->writeln($schema["name_en"][0]);
        foreach(($schema["name_en"] ?? array()) as $name_part) {
            $this->locationName = $this->locationName.get($geoData, $name_part, "");
        }
        foreach(($schema["name_jp"] ?? array()) as $name_part) {
            $this->locationNameJA = $this->locationNameJA.get($geoData, $name_part, "");
        }
        $this->population = get($geoData, $schema["population"], "");
        $this->wiki = get($geoData, $schema["wiki"], "");
		$this->latitude = $latLng["lat"] ?? "";
        $this->longitude = $latLng["lng"] ?? "";
    }*/
    function __construct($geoData) {
       
        $this->locationName = $this->parseGoogleLocationName(get($geoData, "results[0].address_components"));
        
        //$this->population = get($geoData, $schema["population"], "");
        //$this->wiki = get($geoData, $schema["wiki"], "");
		$this->latitude = get($geoData, "results[0].geometry.location.lat");
        $this->longitude = get($geoData, "results[0].geometry.location.lng");
        $out = new \Symfony\Component\Console\Output\ConsoleOutput();
        $out->writeln($this->longitude);
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

    public function toArray() {
        return [
            'locationName' => $this->getLocationName(),
            'locationNameJA' => $this->getLocationNameJA(),
            'wiki' => $this->getWiki(),
            'population' => $this->getPopulation(),
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