<template>
    <div class="col col-pv" >
        <div class="restaurant-container" v-if="restaurants">     
            <div class="list-group restaurant-info-container" v-if='selected.name'>
                <div class="list-group-item active">
                    <h5 v-if="selected.name">{{ selected.name }} </h5>
                </div>
                
                <div>
                    <span class="font-weight-bold">Address: </span>
                    <span class="restaurant-info" v-if="selected.formatted_address">{{ selected.formatted_address }}</span>
                </div>
                <div v-if="selected.opening_hours">
                    <span  class="font-weight-bold">Hours: </span>
                    <span  class="restaurant-info" v-for="text in selected.opening_hours.weekday_text">
                        {{ text }}
                    </span>
                </div>
                <div v-if="selected.international_phone_number">
                    <span class="font-weight-bold">Phone: </span>
                    <span class="restaurant-info" >{{ selected.international_phone_number }}</span>
                </div>
                <span v-if="selected.name" class="btn btn-primary" v-on:click="cancelSelection()">BACK</span>
            </div>
           

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
                restaurants : [],
                page : 0,
                selected : {}
            }
        },
        props  : {
            location: Object
        },
        methods : {
            selectRestaurant(restaurant) {
                this.$emit('getLocation', restaurant)
                axios.get("api/location/google/"+restaurant.place_id)
                    .then(response => {
                        this.selected = response.data.result
                        console.log(this.selected); 
                        this.$forceUpdate(); 
                })
            },
            cancelSelection() {
                this.$emit('cancelSelection');
                
                this.selected = {};
            }
        },
        watch : {
            location : {
                handler(newval) {
                    this.restaurants = [];
                    this.selected = {};
                    this.page = 0;
                    axios.get("api/location/restaurants/"+this.location.latitude+"/"+this.location.longitude)
                    .then(response => {
                        this.restaurants = response.data.results
                        this.$forceUpdate(); 
                    })
                }
            },
        }
            
            
    }
    
</script>