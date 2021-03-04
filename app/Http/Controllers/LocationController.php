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
	// Max times for retrying geodata request
	const MAX_GEODATA_RETRY = 5;
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

		if(Cache::get($postnumber) !== null) {
			return response()->json(Cache::get($postnumber));
		};
		
		try {
			$api_url = sprintf(get($this->location_apis,"google_api.geocode_api"), $postnumber, get($this->location_apis,"google_api.api_key"));
			$json = json_decode($this->client->request('GET', $api_url)
			->getBody()->getContents(), true);
			$location = new Location($json);	
			return response()->json($location->toArray());
			}
		catch (\Exception $e) {		
			return response(self::ERROR_LOCATION_NOT_FOUND, 204);
		}
	}

	// Request weather data from 7Timer
	public function getWeatherData($lat, $lng) {

		if(Cache::get("weather".$lat.$lng) !== null) {
			return response(Cache::get("weather".$lat.$lng));
		};

		try {
			$url = sprintf(get($this->location_apis,"weather_api.api"), $lat, $lng);
			$out = new \Symfony\Component\Console\Output\ConsoleOutput();
			$out->writeln($url);	
			$data = json_decode($this->client->request('GET', $url)
			->getBody()->getContents(), true);
			
			$weatherDataArray = array();
			foreach ($data["daily"] as $val){	
			 	array_push($weatherDataArray, $weather->toArray());
			}
			Cache::put("weather".$lat.$lng, $weatherDataArray, self::CACHE_DURATION);
			return response()->json($weatherDataArray);
		}
		catch (\Exception $e) {
			return response(self::ERROR_WEATHER_DATA_NOT_FOUND, 204);
		}

	}

	public function getNearbyRestaurants($lat, $lng) {
		$url = sprintf(get($this->location_apis,"google_api.restaurants_api"), $lat, $lng, get($this->location_apis,"google_api.api_key"));
		$restaurantsData = $this->client->request('GET', $url)
		->getBody()->getContents();
		return response($restaurantsData);
	}

	public function getPlaceInformation($id) {
		if(Cache::get($id) !== null) {
			return response(Cache::get($id));
		};
		$url = sprintf(get($this->location_apis,"google_api.places_api"), $id, get($this->location_apis,"google_api.api_key"));
		$placeData = $this->client->request('GET', $url)
		->getBody()->getContents();
		Cache::put($id, $placeData, self::CACHE_DURATION);
		return response($placeData);
	}
}
