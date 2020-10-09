<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
        <title>Location Info</title>
        <link rel="stylesheet" href="{{ asset('css/app.css')  }}">

        
    <style>
        html {
            font-size: 1.5vh;
        }

        #map {
            height: 100%;
            width: 100%;
            left: 0;
            top: 0;
            overflow: hidden;
            float: left;
        }

        .mapcontainer{
            height: 45%;
        }

        .wrapper {
            display: grid;
        }
        .container {
            height: 90vh;
            width: 90vw;
            padding-top: 5vh;
            padding-left: 5vh;
            padding-bottom: 5vh;
            padding-right: 5vh;
            background-color:white;
            margin-top: 5vh;
            margin-bottom: 5vh;

        }
        .weather {
            height: 35%;
        }
        .col{
            display: flex;        
        }
        .col-p {
            padding: 1vh;
            height: 100%;
        }
        
        body{
            background-color: #f5f5f5;
        }

        .border {
            border-width: 10px;
        }
       
        .forecast {
            float:left;
            width:80%;
            height: 100%;
            padding-top:5%;
            text-align:center;
        }

        .dailyForecastContainer {
            width:33%;
            height:100%;
            display:flex;
        }

        .weatherIcon {
            width: 45%;
            height: 45%
        }

        .weatherInfo {
            height: 45%;
            padding-top: 5%;
            display: flex;
            align-items: flex-end;
        }

        .titlerow {
            display:none;
        }

    </style>

    </head>
    <body>
    <script src="{{ asset('js/app.js') }}" ></script>

        <script type="text/javascript">

        // Flag for handling state of location request
        var loading = false;

        // "Translation" Json for weekdays
        var weekdays = {
            "0" : "Sun",
            "1" : "Sat",
            "2" : "Mon",
            "3" : "Tue",
            "4" : "Wed",
            "5" : "Thu",
            "6" : "Fri"
        };

        // Define request endpoints for easy access
        // base
        var api = "api/location/";
        // Location request "api/location/{number}"
        var locationByPostNumber = number => api + number;
        // Weather request for location "api/location/{lat}/{lng}"
        var weatherByLatLng = (lat, lng) => api + "weather/" + lat + "/" + lng;

            function getLocation() {
                
                // Do nothing if currently loading
                if(!loading) {
                    
                    // Set state to loading
                    loading = true;

                    // Clear shown data
                    clearData();

                    // Request location data
                    $.ajax({
                        url: locationByPostNumber($("#code").val()),
                        success: function(data, textStatus, jqXHR){
                            console.log(jqXHR.status);
                            if(jqXHR.status == 204){
                                loading = false;
                                $("#locationNameContainer").append("<div class='alert show fade alert-warning'>No location found for post code</div>");
                                setTimeout(function(){$(".alert").alert('close');}, 2000);
                            }
                            else {
                                $("#infoTitle").show(); 
                                $("#locationName").text(data.locationName); 
                            
                                // Populate additional info part of the page with available data
                                if(data.locationNameJA) {
                                    $("#locationInfo").append("<h4 id='locationNameJA'>Name in kanji: "+data.locationNameJA+"</h4>")
                                }
                                if(data.latitude && data.longitude) {
                                    $("#locationInfo").append("<h4 id='position'>Coordinates: " + data.latitude+ ",  "+data.longitude+"</h4>")
                                }
                                if(data.population) {
                                    $("#locationInfo").append("<h4 id='population'>Population: " + data.population+"</h4>")
                                }
                                if(data.wiki) {
                                    $("#locationInfo").append("<h4>More Information: <a href='https://en.wikipedia.org/wiki/"+data.wiki+"'>Wikipedia</a></h4");
                                }
                                
                                // Request weather info from server
                                getWeatherData(data.latitude,data.longitude);

                                // Show map 
                                $("#mapTitle").show();
                                window.my_map.display(data.latitude,data.longitude);
                            }
                        },
                        error: function(data,textStatus, jqXHR) {
                            $("#locationNameContainer").append("<div class='alert show fade alert-danger'>Service not found!</div>");
                            setTimeout(function(){$(".alert").alert('close');}, 2000);
                        }
                    });
                    
                    $.get(locationByPostNumber($("#code").val()), function( data ) {

                        
                        
                    });
                }

            }

            // Separate function for getting and showing weather data
            function getWeatherData(lat, long) {
                
                // Show loading animation
                $("#weatherLoading").show();

                // GET request for data
                $.get( weatherByLatLng(lat, long), function( data ) {

                    var weatherData = $.parseJSON(data).consolidated_weather;

                    // Generate elements for 3 days forecast
                    for (let index = 0; index < 3; index++) {

                        var containerDiv = $("<div class='dailyForecastContainer justify-content-md-center'></div>");
                        var innerDiv = $("<div class='forecast border'></div>");

                        // Icons are currently loaded from external site (hotlinking was mentioned to be ok for this site)
                        innerDiv.append("<img class='weatherIcon' src=https://www.metaweather.com/static/img/weather/"+weatherData[index].weather_state_abbr+".svg>");

                        var weatherInfoDiv = $("<div class='weatherInfo'></div>");
                        var weatherInfoContentDiv = $("<div style='margin:auto'></div>");

                        weatherInfoContentDiv.append("<p >"+weatherData[index].applicable_date+" "+weekdays[new Date(weatherData[index].applicable_date).getDay()]+"</p>");
                        weatherInfoContentDiv.append("<h4>"+weatherData[index].weather_state_name+"</h4>");
                        weatherInfoContentDiv.append("<p>Min "+Math.round(weatherData[index].min_temp)+"º Max "+Math.round(weatherData[index].max_temp)+"º</p>");

                        weatherInfoDiv.append(weatherInfoContentDiv);
                        innerDiv.append(weatherInfoDiv);
                        containerDiv.append(innerDiv);

                        $("#weatherTitle").show();
                        $("#weather").append(containerDiv);    
                    }

                    // Weather is the last data to be requested, so change the flag for site to not loading. 
                    loading = false;

                    // Stop loading animation after request is handled
                    $("#weatherLoading").hide();

                });
            }

            function clearData() {
                // Hide old elements
                $("#weatherTitle").hide();
                $("#infoTitle").hide();
                $("#mapTitle").hide();
                $("#locationName").text(""); 
                $("#map").empty();
                $("#locationInfo").empty();
                $("div").remove(".dailyForecastContainer");
            }

        </script>
  
        <div id="wrapper" class="container border border-dark rounded">
            <div class="row">
                <div class="col col-p">
                    <form class="w-100" onsubmit="event.preventDefault(); getLocation();">
                        <div class="form-row">
                            <div class="w-25">
                                <label for="code" class="col-form-label col-form-label-lg">Post Code</label>
                            </div>
                            <div class="w-50">
                                <input type="text" class="form-control col-sm-10" title="Input valid Japanese post-code. Format is XXX-XXXX, eg. 160-0022" pattern="\d{3}-\d{4}" id="code" aria-describedby="basic-addon3">
                            </div>                         
                            <div class="w-25">
                                <input class="btn btn-primary btn-block" type="submit" value="Submit">
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <div class="row">
                <div class="col col-p" id="locationNameContainer">
                    <h3 id="locationName"></h3>
                </div>
            </div>

            <div class="row">
                <div class="col">
                    <h6 id="weatherTitle" style="display:none">3-day forecast</h6>
                </div>
            </div>
            
            <div class="row weather">
                <div id="weather" class="col col-p">    
                    <div id="weatherLoading" class="spinner-border m-5" role="status" style="display:none">
                    <span class="sr-only">Loading...</span>
                    </div>
                </div>
            </div>


            <div class="row">
                <div class="col">
                    <h6 id="mapTitle" style="display:none">Map</h6>
                </div>
                <div class="col">
                    <h6 id="infoTitle" style="display:none">Additional info</h6>
                </div>
            </div>

            <div class="row mapcontainer">
                <div class="col col-p">
                    <div id="map"></div>
                </div>
                <div id="locationInfo" class="col" style="display:inline">
                   
                </div>
            </div>
        </div>
    </body>
</html>