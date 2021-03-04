<template>

    <div class="row weather">
            <div id="weather" class="col col-p">    
                    <span v-show="error.getError" class="alert alert-warning">Coudln't get weather data.</span>
                    <div v-show="!error.getError && !loading && location" v-for="item in weather" class='dailyForecastContainer justify-content-md-center'>
                        <div class='forecast border'>
                            <img class='weatherIcon' :src="item.img"/>
                            <div class='weatherInfo'>
                                <div style='margin:auto'>
                                    <p >{{moment(item.date)}}</p> 
                                    <h4>{{item.weather}}</h4>
                                    <p>Max {{Math.round(item.max)}} º Min {{Math.round(item.min)}}º</p>
                                </div>
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
        data() {
            return {
                weather : [],
                loading : false,
                error : {
                    getError : false
                }
            }
        },
        props : {
            location : Object
        },
        methods: {
            moment: function (item) {
                // Moment seems to be mutable, so we need to assign it's return value to a variable
                let i = moment(item*1000).format("YYYY-MM-DD dddd");
                return i;
            }
        },
        watch : {        
            location : {
                handler() {    
                    this.loading = true;
                    this.error.getError = false;
                    // Find weather for the newly detected location
                    axios.get("api/location/weather/" + this.location.latitude + "/" + this.location.longitude).then(response => {
                        this.weather = [];
                        if(response.status == 200){ 
                            // Show just 3 of the results (3 day forecast)
                            for (let index = 0; index < 3; index++) {
                                response.data[index].img = window.location.href+'images/icons/'+response.data[index].img+'.svg';
                                this.weather.push(response.data[index]);
                            }
                        }
                        else {
                                this.error.getError = true;
                            }
                        this.loading = false;
                    });
                },
                deep : true
            }
        }
    }
</script>

