<template>

    <div class="row weather">
            <div id="weather" class="col col-p">    
                    <div v-show="!loading && location" v-for="item in weather" class='dailyForecastContainer justify-content-md-center'>
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
                loading : false
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
                handler(newval) {
                    this.loading = true;
                    axios.get("api/location/weather/" + this.location.latitude + "/" + this.location.longitude).then(response => {
                        this.weather = [];
                        for (let index = 0; index < 3; index++) {
                            response.data[index].img = window.location.href+'images/icons/'+response.data[index].img+'.svg';
                            this.weather.push(response.data[index]);
                        }
                        this.loading = false;
                    });
                },
                deep : true
            }
        }
    }
</script>

