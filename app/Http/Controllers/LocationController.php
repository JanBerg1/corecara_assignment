<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;


use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Exception\ClientException;

use Cache;

use App\Models\Location;
use Exception;

class LocationController extends Controller
{
	// Max times for retrying geodata request
	const MAX_GEODATA_RETRY = 5;
	const CACHE_DURATION = 600;

	// Define external APIs
	const SEVENTIMER_API = "http://7timer.info/bin/civillight.php";
	const GEOCODEXYZ_API = 'https://geocode.xyz/';
	const ZIPPOPOTAMUS_API = 'http://api.zippopotam.us/jp/';
	const GEONAMES_API = 'http://api.geonames.org/';

	// Define error response messages
	const ERROR_LOCATION_NOT_FOUND = "Location not found!";
	const ERROR_WEATHER_DATA_NOT_FOUND = "Cannot fetch weather data.";
	const ERROR_SERVER_BUSY = "Could not access data, try again in a moment.";

    // GET for location data using post code
	public function getLocationDataByPostNumber($postnumber) {

		if(Cache::get($postnumber) !== null) {
			return response()->json(Cache::get($postnumber));
		};


		$latLng = array();

		// Due to some lack of data in a single API (Zippopotamus), use Geonames as a backup if location cant be found from Zippopotamus. 
		try{
			$latLng = $this->RequestZippopotamusData($postnumber);
		}
		catch(\Exception $e){
			try{
				$latLng = $this->RequestGeonamesData($postnumber);
			}
			catch(\Exception $e) {
				// Return response to notify client that location could not be found with given post code.
				return response(self::ERROR_LOCATION_NOT_FOUND, 204);
			}
		}

		$retry = 0;
		$geocodeData;

		// Free API requests to Geocode seems to get refused sometimes. Retry few times in case of failed request.
		while($retry < self::MAX_GEODATA_RETRY && !(isset ($geocodeData))) {
			try {
				$geocodeData = $this->RequestGeocodeData($latLng);
			}
			catch(\Exception $e){
				// Wait 2 seconds before retrying;
				sleep(1);
				$retry ++;
			}
		}

		// If max retries were reached, return 503 reponse
		if($retry == self::MAX_GEODATA_RETRY) {
			return response(self::ERROR_SERVER_BUSY, 503);
		}

		// If location data was found, return the data. Otherwise send response for data not found.
		if(isset ($geocodeData)){
			$location = new Location($geocodeData, $latLng);
			Cache::put($postnumber, $location->toJSONArray(), self::CACHE_DURATION);
			return response()->json($location->toJSONArray());
		}
		else {
			return response(self::ERROR_LOCATION_NOT_FOUND, 204);
		}
	}

	// Request weather data from 7Timer
	public function getWeatherData($lat, $long) {

		if(Cache::get("weather".$lat.$long) !== null) {
			return response()->json(Cache::get("weather".$lat.$long));
		};

		$client = new Client();

		try {

			$weatherData = $client->request('GET', self::SEVENTIMER_API."?lon=".$long."&lat=".$lat."&ac=0&unit=metric&output=json&tzshift=0")
			->getBody()->getContents();
			Cache::put("weather".$lat.$long, $weatherData, self::CACHE_DURATION);
			return response($weatherData);;
			
		}
		catch (\Exception $e) {
			return response(self::ERROR_WEATHER_DATA_NOT_FOUND, 204);
		}

	}

	// Request location data from Geocode.xyz
	private function RequestGeocodeData($latLng) {
		try {
			$client = new Client();
			return json_decode($client->request('GET', self::GEOCODEXYZ_API.$latLng["lat"].
			','.$latLng["lng"].'?geoit=json')
			->getBody()->getContents(), true);
		}
		catch(\Exception $e){
			throw new Exception();
		}
	}

	// Request location data by post code from Zippopotamus
	private function RequestZippopotamusData($postnumber) {
		try {
			$client = new Client();
			$json = json_decode($client->request('GET', self::ZIPPOPOTAMUS_API.$postnumber)
			->getBody()->getContents(), true);
			return array(
				"lat" => $json['places'][0]['latitude'],
				"lng" => $json['places'][0]['longitude'],
			);
		}
		catch (\Exception $e){
			throw new Exception();
		}
	}

	// Request location data by post code from Geonames
	private function RequestGeonamesData($postnumber) {
		try {
			$client = new Client();
			$json = json_decode($client->request('GET', self::GEONAMES_API.'postalCodeSearchJSON?postalcode='.$postnumber.'&username=locationtask')
			->getBody()->getContents(),true);
			return $latLng = array(
				"lat" => $json["postalCodes"][0]["lat"],
				"lng" => $json["postalCodes"][0]["lng"],
			);
		}
		catch (\Exception $e){
			throw new Exception();
		}
	}
	
}
