<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;


use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Exception\ClientException;

use App\Models\Location;
use Exception;

class LocationController extends Controller
{
    // GET for location data using post code
	public function getLocationDataByPostNumber($postnumber) {

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
				return response("Location not found!", 204);
			}
		}

		$retry = 0;
		$geocodeData;

		// Free API requests to Geocode seems to get refused sometimes. Retry few times in case of failed request.
		while($retry < 5 && !(isset ($geocodeData))) {
			try {
				$geocodeData = $this->RequestGeocodeData($latLng);
			}
			catch(\Exception $e){
				$retry ++;
			}
		}

		// If location data was found, return the data. Otherwise send response for data not found.
		if(isset ($geocodeData)){
			$location = new Location($geocodeData, $latLng);
			return response()->json($location->toJSONArray());
		}
		else {
			return response("Location data not found!", 204);
		}
	}

	public function getWeatherData($lat, $long) {
		$client = new Client();

		try {
			// Metaweather uses their own woeid (location id) for getting weather data. We need to find this id by requesting location data by lat,long first.
			$weatherData = json_decode($client->request('GET', 'https://www.metaweather.com/api/location/search/?lattlong='.$lat.','.$long)
			->getBody()->getContents());

			// Request weather data with found woeid.
			return response($client->request('GET', 'https://www.metaweather.com/api/location/'.$weatherData[0]->{'woeid'})
			->getBody()->getContents());
		}
		catch (\Exception $e) {
			return response("Cannot fetch weather data.", 204);
		}

	}

	private function RequestGeocodeData($latLng) {
		try {
			$client = new Client();
			return json_decode($client->request('GET', 'https://geocode.xyz/'.$latLng["lat"].
			','.$latLng["lng"].'?geoit=json')
			->getBody()->getContents(), true);
		}
		catch(\Exception $e){
			throw new Exception("Something went wrong.");
		}
	}

	private function RequestZippopotamusData($postnumber) {
		try {
			$client = new Client();
			$json = json_decode($client->request('GET', 'http://api.zippopotam.us/jp/'.$postnumber)
			->getBody()->getContents(), true);
			return array(
				"lat" => $json['places'][0]['latitude'],
				"lng" => $json['places'][0]['longitude'],
			);
		}
		catch (\Exception $e){
			throw new Exception("Location not found");
		}
	}

	private function RequestGeonamesData($postnumber) {
		try {
			$client = new Client();
			$json = json_decode($client->request('GET', 'http://api.geonames.org/postalCodeSearchJSON?postalcode='.$postnumber.'&username=locationtask')
			->getBody()->getContents(),true);
			return $latLng = array(
				"lat" => $json["postalCodes"][0]["lat"],
				"lng" => $json["postalCodes"][0]["lng"],
			);
		}
		catch (\Exception $e){
			throw new Exception("Location not found");
		}
	}
	
}
