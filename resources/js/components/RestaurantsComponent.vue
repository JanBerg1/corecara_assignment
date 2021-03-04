<template>
    <div class="col col-pv" >

        <div v-show="loading" id="locationLoading" class="spinner-border m-5" role="status">
            <span class="sr-only">Loading...</span>
        </div>

        <div class="restaurant-container">    
             
            <div class="list-group restaurant-info-container" v-show='selected.name'>
                <div class="list-group-item active">
                    <h5 v-show="selected.name">{{ selected.name }} </h5>
                </div>
                
                <div v-show="selected.formatted_address">
                    <span class="font-weight-bold">Address: </span>
                    <span class="restaurant-info" >{{ selected.formatted_address }}</span>
                </div>
                <div v-if="selected.opening_hours">
                    <span  class="font-weight-bold">Hours: </span>
                    <span  class="restaurant-info" v-for="text in selected.opening_hours.weekday_text">
                        {{ text }}
                    </span>
                </div>
                <div v-show="selected.international_phone_number">
                    <span class="font-weight-bold">Phone: </span>
                    <span class="restaurant-info" >{{ selected.international_phone_number }}</span>
                </div>
            </div>

            <span v-show="selected.name" class="btn-restaurant-cancel btn btn-primary" v-on:click="cancelSelection()">BACK</span>
    
            <div class="restaurant list-group" v-if="!selected.name && restaurants.length != 0">
                <div v-on:click="selectRestaurant(restaurant)" class="list-group-item list-group-item-action" v-for="restaurant in restaurants.slice(page*6, page*6+6)">
                    <span>{{ restaurant.name }}</span>
                </div>
                <div>
                    <div style="display:flex">
                        <span v-if="page!=0" class="btn btn-primary restaurant-page" v-on:click="page--">Previous</span>
                        <span v-if="!((page+1)*6 >= restaurants.length)" class="btn btn-primary restaurant-page" v-on:click="page++">Next</span>
                    </div>
                </div>
            </div>
            <div class="restaurant-alert" v-show="!loading && this.location.locationName && restaurants.length == 0">
                <span class="alert alert-warning">No restaurants found near this location.</span>
            </div>
        </div>
        
    </div>
</template>


<script>

    export default {
        mounted() {
            console.log('Component mounted.')
        },
        data(){
            return {
                restaurants : Array,
                page : 0,
                selected : {},
                loading : false,
            }
        },
        props  : {
            location: Object
        },
        methods : {
            selectRestaurant(restaurant) {
                // Emit to let know that we have selected restaurant to show
                this.$emit('getLocation', restaurant)

                // Get more data for restaurant
                axios.get("api/location/google/"+restaurant.place_id)
                    .then(response => {
                        this.selected = response.data.result
                })
            },
            cancelSelection() {
                // Emit to let know that we are not looking at the selected restaurant anymore
                this.$emit('cancelSelection');
                this.selected = {};
            }
        },
        watch : {
            location : {
                handler() {
                    // Reset restaurants list data when location is changed
                    this.loading = true;
                    this.restaurants = [];
                    this.selected = {};
                    this.page = 0;

                    // Find "nearby" restaurants by location
                    axios.get("api/location/restaurants/"+this.location.latitude+"/"+this.location.longitude)
                    .then(response => {
                        this.restaurants = response.data.results
                        this.loading = false;
                    })
                }
            },
        }
            
            
    }
    
</script>