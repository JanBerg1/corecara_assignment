<?php

namespace App\Models;

use function _\get;

// "Model" class for wrapping location data from used APIs
class Weather  {

    private $img;
    private $max;
    private $min;
    private $weather;
    private $date;

    function __construct($weatherData) {
       
        $this->img = get($weatherData, "weather[0].icon");
		$this->max = get($weatherData, "temp.max");
        $this->min = get($weatherData, "temp.min");
        $this->weather = get($weatherData, "weather[0].main");
        $this->date = get($weatherData, "dt");
    }

    public function getImg(){
        return $this->img;
    }
    public function getMax(){
        return $this->max;
    }
    public function getMin(){
        return $this->min;
    }
    public function getWeather(){
        return $this->weather;
    }
    public function getDate(){
        return $this->date;
    }

    public function toArray() {
        return [
            'img' => $this->getImg(),
            'max' => $this->getMax(),
            'min' => $this->getMin(),
            'weather' => $this->getWeather(),
            'date' => $this->getDate()
        ];
    }
}

?>