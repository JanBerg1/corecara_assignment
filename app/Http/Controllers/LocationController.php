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
	const CACHE_DURATION = 6000;

	// Define error response messages
	const ERROR_LOCATION_NOT_FOUND = "Location not found!";
	const ERROR_WEATHER_DATA_NOT_FOUND = "Cannot fetch weather data.";
	const ERROR_SERVER_BUSY = "Could not access data, try again in a moment.";

    // GET for location data using post code
	public function getLocationDataByPostNumber($postnumber) {

		if(Cache::get($postnumber) !== null) {
			return response()->json(Cache::get($postnumber));
		};

		$latLng;

		// Due to some lack of data in a single API (Zippopotamus), use Geonames as a backup if location cant be found from Zippopotamus. 

		$location_apis = Storage::disk('local')->get('location_api.json');
		$location_apis = json_decode($location_apis, true);	
		$api = $location_apis["google_api"];

		
	
		return response()->json($this->RequestLocationDataByZip($api,$postnumber)->toArray());
	}

	// Request weather data from 7Timer
	public function getWeatherData($lat, $lng) {
		if(Cache::get("weather".$lat.$lng) !== null) {
			return response(Cache::get("weather".$lat.$lng));
		};


		$client = new Client();
		$api = Storage::disk('local')->get('weather_api.json');
		$api = json_decode($api, true);	

		
		try {

			$url = sprintf(get($api, "apis[0].api"), $lat, $lng);
			
			$weatherData = $client->request('GET', $url)
			->getBody()->getContents();
			Cache::put("weather".$lat.$lng, $weatherData, self::CACHE_DURATION);
			return response($weatherData);
			
		}
		catch (\Exception $e) {
			return response(self::ERROR_WEATHER_DATA_NOT_FOUND, 204);
		}

	}

	public function getNearbyRestaurants($lat, $lng) {
		$client = new Client();
		$location_api = Storage::disk('local')->get('location_api.json');
		$location_api = json_decode($location_api, true);	
		$api = $location_api["google_api"];
		$url = sprintf($api["restaurants_api"], $lat, $lng, $api["api_key"]);
		$out = new \Symfony\Component\Console\Output\ConsoleOutput();
		$out->writeln($url);
		$restaurantsData = $client->request('GET', $url)
		->getBody()->getContents();
		return response($restaurantsData);
	}

	public function getPlaceInformation($id) {
		if(Cache::get($id) !== null) {
			return response(Cache::get($id));
		};
		$client = new Client();
		$location_api = Storage::disk('local')->get('location_api.json');
		$location_api = json_decode($location_api, true);	
		$api = $location_api["google_api"];
		$url = sprintf($api["places_api"], $id, $api["api_key"]);
		
		$out = new \Symfony\Component\Console\Output\ConsoleOutput();
		$out->writeln($url);
		$placeData = $client->request('GET', $url)
		->getBody()->getContents();
		Cache::put($id, $placeData, self::CACHE_DURATION);
		return response($placeData);
	}
	
	// Request location data by post code from external api
	private function RequestLocationDataByZip($api, $postnumber) {
		try {
			$client = new Client();
			$api_url = sprintf($api["geocode_api"], $postnumber, $api["api_key"]);
			$json = json_decode($client->request('GET', $api_url)
			->getBody()->getContents(), true);	
			return new Location($json);
		}
		catch (\Exception $e){
			#throw new Exception();
		}
	}

	

}
