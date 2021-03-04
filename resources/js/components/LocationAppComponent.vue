<template>
    <div class="container border border-dark rounded" >
    
                    <div class="row">
                        <div class="col col-p">
                            <form class="w-100" @submit="getLocation">
                                <div class="form-row">
                                    <div class="w-25">
                                        <label for="code" class="col-form-label col-form-label-lg">Post Code</label>
                                    </div>
                                    <div class="w-50">
                                        <input type="text" v-model="zipCode" class="form-control col-sm-10" title="Input valid Japanese post-code. Format is XXX-XXXX, eg. 160-0022" pattern="\d{3}-\d{4}" id="code" aria-describedby="basic-addon3">
                                    </div>                         
                                    <div class="w-25">
                                        <input class="btn btn-primary btn-block" type="submit" value="Submit">
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    <div v-show="error.locationNotFound" class='alert show fade alert-warning'>No location found for post code</div>
                    <div class="row">
                        <div v-show="loading" id="locationLoading" class="spinner-border m-5" role="status">
                            <span class="sr-only">Loading...</span>
                        </div>
                        <div v-show="!loading" class="col col-p" id="locationNameContainer">
                            
                            <h3>{{ locationData.locationName }}</h3>
                        </div>
                    </div>

                    <div  class="row">
                                    <div v-show="!loading && locationData.latitude" class="col">
                                        <h6 id="weatherTitle">3-day forecast</h6>
                                    </div>
                                </div>

                    <weather-component v-show="!loading && locationData.latitude" v-bind:location="locationData"></weather-component>

                    <div class="row">
                        <div class="col" v-show="!loading">
                            <h6 id="mapTitle" v-show="locationData.latitude"> Map</h6>
                        </div>
                        <div class="col" v-show="!loading">
                            <h6 id="infoTitle" v-show="locationData.latitude">Nearby restaurants (click for info)</h6>
                        </div>
                    </div>

                    <div v-show="!loading && locationData.latitude" class="row mapcontainer">
                        <map-component v-bind:location="locationData" v-bind:pins="pins"></map-component>
                        <restaurants-component v-on:getLocation="pinRestaurant" v-on:cancelSelection="removePin" v-bind:location="locationData"></restaurants-component>
                    </div>

                </div>   
</template>

<script>
import MapComponent from './MapComponent.vue';
import RestaurantsComponent from './RestaurantsComponent.vue';
import WeatherComponentVue from './WeatherComponent.vue'
    export default {
        mounted() {
            console.log('Component mounted.');
        },
        components: {
            "weather-component" : WeatherComponentVue,
            "map-component" : MapComponent,
                RestaurantsComponent
        },
        data() {
                return {
                        pins : [],
                        zipCode : "",
                        locationData : {},
                        loading : false,
                        error : {
                            locationNotFound : false
                        }
                }
        },
        methods: {
            getLocation : function(e) {
                e.preventDefault();
                this.loading = true;
                axios.get("api/location/" + this.zipCode)
                .then(response => {
                    this.loading = false;  
                    if(response.status == 204){
                        this.error.locationNotFound = true;
                        this.locationData = {};
                        setTimeout(() => this.error.locationNotFound = false, 2000);
                    }
                    else{
                        this.locationData = response.data;                           
                    }
                });
                
            },
            pinRestaurant : function(value) {
                this.pins = []
                this.pins.push(
                    {
                        location : {
                            lat : value.geometry.location.lat,
                            lng : value.geometry.location.lng
                        }
                    }
                )
            },
            removePin : function(value) {
                this.pins = []
            }
        }
    }
</script>
