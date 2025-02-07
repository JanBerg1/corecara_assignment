/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */

require('./bootstrap');
window.moment = require("moment");

window.Vue = require('vue');

/**
 * The following block ofecursively scan this directory for the Vue
 * components and automatically register them with their "basename".
 *
 * Eg. ./components/ExampleComponent.vue -> <example-component></example-component>
 */

// const files = require.context('./', true, /\.vue$/i)
// files.keys().map(key => Vue.component(key.split('/').pop().split('.')[0], files(key).default))

Vue.component('location-app-component', require('./components/LocationAppComponent.vue').default);
Vue.component('weather-component', require('./components/WeatherComponent.vue').default);
Vue.component('map-component', require('./components/MapComponent.vue').default);
Vue.component('restaurants-component', require('./components/RestaurantsComponent.vue').default);

import * as VueGoogleMaps from 'vue2-google-maps'

Vue.use(VueGoogleMaps, {
    load: {
      key: 'AIzaSyDEJy0YymYo1ejNbzyA8ivWh1r4Ukdev48'
    },
})

/**
 * Next, we will create a fresh Vue application instance and attach it to
 * the page. Then, you may begin adding components to this application
 * or customize the JavaScript scaffolding to fit your unique needs.
 */

const app = new Vue({
    el: '#app'
});
