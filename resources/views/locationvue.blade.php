<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
        <title>Location Info</title>
        <link rel="stylesheet" href="{{ asset('css/app.css')  }}">
        <link rel="stylesheet" href="{{ asset('css/location.css') }}">
        <script src="{{ asset('js/location.js') }}" ></script>
    </head>
    <body>
        <div id="app">
            <example-component></example-component>
        </div>

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
                    <div id="locationLoading" class="spinner-border m-5" role="status" style="display:none">
                        <span class="sr-only">Loading...</span>
                    </div>
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
        <script src="{{ asset('js/app.js') }}" ></script>
        <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyDEJy0YymYo1ejNbzyA8ivWh1r4Ukdev48&callback=initMap&libraries=&v=weekly"async></script>
    </body>
</html>
