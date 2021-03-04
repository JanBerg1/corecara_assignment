<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Exception\ClientException;

use Cache;
use Storage;

use App\Models\Location;
use App\Models\Weather;
use Exception;

use function _\get;

class LocationController extends Controller
{
	const CACHE_DURATION = 6000;

	// Define error response messages
	const ERROR_LOCATION_NOT_FOUND = "Location not found!";
	const ERROR_WEATHER_DATA_NOT_FOUND = "Cannot fetch weather data.";
	const ERROR_SERVER_BUSY = "Could not access data, try again in a moment.";

	private Client $client;
	private $location_apis;

	public function __construct(){
		$this->client = new Client();
		$this->location_apis = Storage::disk('local')->get('location_api.json');
		$this->location_apis = json_decode($this->location_apis, true);
		
	}
    
    // GET for location data using post code
	public function getLocationDataByPostNumber($postnumber) {

		// No caching for Google APIs as per the Google API terms of service (No caching allowed basically)
		
		try {
			// Build url from api.json and use that to request location
			$api_url = sprintf(get($this->location_apis,"google_api.geocode_api"), $postnumber, get($this->location_apis,"google_api.api_key"));
			$json = json_decode($this->client->request('GET', $api_url)
			->getBody()->getContents(), true);
			// Wrap googles location data to our Location object
			$location = new Location($json);	
			return response()->json($location->toArray());
			}
		catch (\Exception $e) {		
			return response(self::ERROR_LOCATION_NOT_FOUND, 204);
		}
	}

	// GET for location data using latitude and longitude
	public function getWeatherData($lat, $lng) {
		
		// Use cache for weather, is not from Google's API afterall
		if(Cache::get("weather".$lat.$lng) !== null) {
			return response()->json(Cache::get("weather".$lat.$lng));
		};

		try {
			// Build url from api.json and use that to request weather
			$url = sprintf(get($this->location_apis,"weather_api.api"), $lat, $lng, get($this->location_apis,"weather_api.api_key"));
			$data = json_decode($this->client->request('GET', $url)->getBody()->getContents(), true);
			$weatherDataArray = array();

			// Wrap weather data to Weather objects
			foreach ($data["daily"] as $val){	
				$weather = new Weather($val);
			 	array_push($weatherDataArray, $weather->toArray());
			}
			Cache::put("weather".$lat.$lng, $weatherDataArray, self::CACHE_DURATION);
			return response()->json($weatherDataArray);
		}
		catch (\Exception $e) {
			return response(self::ERROR_WEATHER_DATA_NOT_FOUND, 204);
		}

	}

	// Get restaurants near given latitude and longitude
	public function getNearbyRestaurants($lat, $lng) {
		// Build url from api.json and use that to request nearby restaurants
		$url = sprintf(get($this->location_apis,"google_api.restaurants_api"), $lat, $lng, get($this->location_apis,"google_api.api_key"));
		$restaurantsData = $this->client->request('GET', $url)->getBody()->getContents();
		return response($restaurantsData);
	}

	// Get place informationn from Google Places API by google's place ID
	public function getPlaceInformation($id) {
		// Build url from api.json and use that to request place data
		$url = sprintf(get($this->location_apis,"google_api.places_api"), $id, get($this->location_apis,"google_api.api_key"));
		$placeData = $this->client->request('GET', $url)->getBody()->getContents();
		return response($placeData);
	}
}
