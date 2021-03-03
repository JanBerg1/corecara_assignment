<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Exception\ClientException;

use Cache;
use Storage;

use App\Models\Location;
use Exception;

use function _\get;

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

		/*if(Cache::get($postnumber) !== null) {
			return response()->json(Cache::get($postnumber));
		};*/

		$latLng;

		// Due to some lack of data in a single API (Zippopotamus), use Geonames as a backup if location cant be found from Zippopotamus. 

		$location_apis = Storage::disk('local')->get('location_api.json');
		$location_apis = json_decode($location_apis, true);	
		$apis = $location_apis["zip_apis"];

		foreach($apis as $api) {
			if(isset($latLng)){
				break;
			}
			$latLng = $this->RequestLocationDataByZip($api,$postnumber);
		}
		
		if(!isset($latLng)){
			return response(self::ERROR_LOCATION_NOT_FOUND, 204);
		}

		
		$retry = 0;	
		// Free API requests to Geocode seems to get refused sometimes. Retry few times in case of failed request.		
		$apis = $location_apis["coord_apis"];		
		$geocodeData;
		foreach($apis as $api) {
			if(isset($geocodeData)){
				break;
			}	
			while($retry < self::MAX_GEODATA_RETRY && !(isset ($geocodeData))) {
				try {
					$geocodeData = $this->RequestLocationDataByCoord($api, $latLng);
				}
				catch(\Exception $e){
					// Wait 2 seconds before retrying;
					sleep(1);
					$retry ++;
					$out = new \Symfony\Component\Console\Output\ConsoleOutput();
					$out->writeln("CATCHED");
				}
			}	
		}

		// If max retries were reached, return 503 reponse
		if($retry == self::MAX_GEODATA_RETRY) {
			return response(self::ERROR_SERVER_BUSY, 503);
		}

		// If location data was found, return the data. Otherwise send response for data not found.
		if(isset ($geocodeData)){
			$schema = $location_apis["coord_apis"][0]["schema"];
			$location = new Location($geocodeData, $latLng, $schema);
			Cache::put($postnumber, $location->toArray(), self::CACHE_DURATION);
			return response()->json($location->toArray());
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
		$api = Storage::disk('local')->get('weather_api.json');
		$api = json_decode($api, true);	
		$api_urls = $this->buildWeatherAPIUrls($api, $lat, $long);

		try {
			
			//$weatherData = $client->request('GET', self::SEVENTIMER_API."?lon=".$long."&lat=".$lat."&ac=0&unit=metric&output=json&tzshift=0")
			//->getBody()->getContents();
			$weatherData = $client->request('GET', $api_urls[0])
			->getBody()->getContents();
			Cache::put("weather".$lat.$long, $weatherData, self::CACHE_DURATION);
			return response($weatherData);;
			
		}
		catch (\Exception $e) {
			return response(self::ERROR_WEATHER_DATA_NOT_FOUND, 204);
		}

	}

	// Request location data from Geocode.xyz
	private function RequestGeocodeData($api, $latLng) {
		try {
			$client = new Client();
			//return json_decode($client->request('GET', self::GEOCODEXYZ_API.$latLng["lat"].
			//','.$latLng["lng"].'?geoit=json')
			$api_url = sprintf($api["api"], $latLng["lat"], $latLng["lng"]);
			$out = new \Symfony\Component\Console\Output\ConsoleOutput();
			$out->writeln($api_url);
			
			return json_decode($client->request('GET', $api_url)
			->getBody()->getContents(), true);
		}
		catch(\Exception $e){
			throw new Exception();
		}
	}

	private function RequestLocationDataByCoord($api, $latLng) {	
		$client = new Client();
		$location_data;
		$api_url = sprintf($api["api"], $latLng["lat"], $latLng["lng"]);	
		try {
			return $location_data = json_decode($client->request('GET', $api_url)->getBody()->getContents(), true);
		}
		catch(\Exception $e){
			throw new Exception();
		}
	}
			
		
		
		
	

	/*
	// Request location data by post code from Zippopotamus
	private function RequestZippopotamusData($postnumber) {
		try {
			$client = new Client();
			$api = Storage::disk('local')->get('location_api.json');
			$api = json_decode($api, true);	
			$api_url = sprintf(get($api, "zip_apis[0].api"), $postnumber);
			$json = json_decode($client->request('GET', $api_url)
			->getBody()->getContents(), true);
			return array(
				"lat" => get($json, get($api, "zip_apis[0].schema.lat")),
				"lng" => get($json, get($api, "zip_apis[0].schema.lng")),
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
	*/

	private function buildWeatherAPIUrls($json, $lat, $lon) {
		$urls = [];
		foreach ($json['apis'] as $api) {
			$api_url = $api['api']
			.$api['endpoints']['lat_lon']['endpoint']
			.$api['base_params']
			.$api['endpoints']['lat_lon']['lat']
			.$api['endpoints']['lat_lon']['lon'];
			$api_url = sprintf($api_url, $lat, $lon);
			array_push($urls, $api_url);
		}	
		return $urls;
	}
	
	// Request location data by post code from external api
	private function RequestLocationDataByZip($api, $postnumber) {
		try {
			$client = new Client();
			$api_url = sprintf($api["api"], $postnumber);
			$json = json_decode($client->request('GET', $api_url)
			->getBody()->getContents(), true);	
			return array(
				"lat" => get($json, get($api, "schema.lat")),
				"lng" => get($json, get($api, "schema.lng")),
			);
		}
		catch (\Exception $e){
			#throw new Exception();
		}
	}

}
