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

                    <div class="row">
                        <div class="col col-p" id="locationNameContainer">
                            <div v-if="loading" id="locationLoading" class="spinner-border m-5" role="status">
                                <span class="sr-only">Loading...</span>
                            </div>
                            <h3>{{ locationName }}</h3>
                        </div>
                    </div>
                   

                    <div  class="row">
                                    <div v-if="!loading && locationData.latitude" class="col">
                                        <h6 id="weatherTitle">3-day forecast</h6>
                                    </div>
                                </div>

                    <weather-component v-bind:location="locationData" v-bind:loading="loading"></weather-component>
                    <div class="row">
                        <div class="col">
                            <h6 id="mapTitle" v-if="locationData.latitude"> Map</h6>
                        </div>
                        <div class="col">
                            <h6 id="infoTitle" v-if="locationData.latitude">Nearby restaurants</h6>
                        </div>
                    </div>
                    <div class="row mapcontainer">
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
                        locationName : "",
                        locationData : {},
                        loading : false
                }
        },
        methods: {
            getLocation : function(e) {
                e.preventDefault();
                this.loading = true;
                axios.get("api/location/" + this.zipCode)
                .then(response => {
                    this.locationData = response.data;
                    this.locationName = response.data.locationName;
                    this.loading = false;     
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
