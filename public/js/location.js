// Flag for handling state of location request
var loading = false;

// "Translation" Json for weekdays
var weekdays = {
    "0" : "Sun", 
    "1" : "Mon",
    "2" : "Tue",
    "3" : "Wed",
    "4" : "Thu",
    "5" : "Fri",
    "6" : "Sat"
};

//  Json for icon names
var weatherImages =
{
    "clear" : "c",
    "pcloudy" : "lc",
    "mcloudy" : "hc",
    "cloudy" : "hc",
    "humid" : "lc",
    "lightrain" : "lr",
    "oshower" : "s",
    "ishower" : "s",
    "lightsnow" : "sn",
    "rain" : "hr",
    "snow" : "sn",
    "rainsnow" : "sl",
    "ts" : "t",
    "tsrain" : "t"
};

// Json for showed weather names
var weatherNames =
{
    "clear" : "Clear",
    "pcloudy" : "Light Cloud",
    "mcloudy" : "Cloudy",
    "cloudy" : "Cloudy",
    "humid" : "Humid",
    "lightrain" : "Light Rain",
    "oshower" : "Showers",
    "ishower" : "Showers",
    "lightsnow" : "Showers",
    "rain" : "Rain",
    "snow" : "Snow",
    "rainsnow" : "Snow",
    "ts" : "Thunderstorm",
    "tsrain" : "Thunderstorm"
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

            $("#locationLoading").show();

            // Request location data
            $.ajax({
                url: locationByPostNumber($("#code").val()),
                success: function(data, textStatus, jqXHR){
                    $("#locationLoading").hide();
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
                            $("#locationInfo").append("<h4>More Information: <a href='https://en.wikipedia.org/wiki/"+data.wiki+"' target='blank'>Wikipedia</a></h4");
                        }
                        
                        // Request weather info from server
                        getWeatherData(data.latitude,data.longitude);

                        // Show map 
                        $("#mapTitle").show();
                        window.my_map.display(data.latitude,data.longitude);
                    }
                },
                // Handle failed request
                error: function(xhr, textStatus, errorThrown) {
                    loading = false;
                    if(xhr.status == 503) {
                        $("#locationNameContainer").append("<div class='alert show fade alert-danger'>Service is busy. Try again in a moment.</div>");
                    }
                    else {
                        $("#locationNameContainer").append("<div class='alert show fade alert-danger'>Failed to fetch data!</div>");
                    }
                    setTimeout(function(){$(".alert").alert('close');}, 2000);
                }
            });
        }

    }

    // Separate function for getting and showing weather data
    function getWeatherData(lat, long) {
        
        // Show loading animation
        $("#weatherLoading").show();

        // GET request for data
        $.ajax({
            url: weatherByLatLng(lat, long),
            success: function(data, textStatus, jqXHR){

                var weatherData = $.parseJSON(data);

                 // Generate elements for 3 days forecast
                 for (let index = 0; index < 3; index++) {

                    var containerDiv = $("<div class='dailyForecastContainer justify-content-md-center'></div>");
                    var innerDiv = $("<div class='forecast border'></div>");

                    // Icons are currently loaded from external site (hotlinking was mentioned to be ok for this site)
                    innerDiv.append("<img class='weatherIcon' src=https://www.metaweather.com/static/img/weather/"+weatherImages[weatherData.dataseries[index].weather]+".svg>");

                    // Generate content 
                    var weatherInfoDiv = $("<div class='weatherInfo'></div>");
                    var weatherInfoContentDiv = $("<div style='margin:auto'></div>");

                    // parse date of type yyyymmdd
                    var date = weatherData.dataseries[index].date.toString();
                    var dateString = date.substring(0,4) + "-" + date.substring(4,6) + "-" + date.substring(6,8);

                    weatherInfoContentDiv.append("<p >"+dateString+" "+weekdays[new Date(dateString).getDay()]+"</p>");
                    weatherInfoContentDiv.append("<h4>"+weatherNames[weatherData.dataseries[index].weather]+"</h4>");
                    weatherInfoContentDiv.append("<p>Min "+weatherData.dataseries[index].temp2m.max+"º Max "+weatherData.dataseries[index].temp2m.min+"º</p>");

                    // Append to containers to display content
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

            },
            // Handle failed request
            error: function(xhr, textStatus, errorThrown) {
                loading = false;
                $("#weatherLoading").hide();
                $("#weatherAppend").append("<div class='alert alert-warning'>Cannot find weather data.</div>");
            }
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

