<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <!--<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>-->
        <title>Location Info</title>
        <link rel="stylesheet" href="{{ asset('css/app.css')  }}">
        <link rel="stylesheet" href="{{ asset('css/location.css') }}">
        <!--<script src="{{ asset('js/location.js') }}" ></script>-->
    </head>
    <body>
        <div id="app">
            <location-app-component></location-app-component>
        </div>
        <script src="{{ asset('js/app.js') }}" ></script>
        <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyDEJy0YymYo1ejNbzyA8ivWh1r4Ukdev48&callback=initMap&libraries=&v=weekly"async></script>
    </body>
</html>
