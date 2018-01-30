/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */

require('./bootstrap');
require('./modules/Global');

window.Vue = require('vue');

Vue.use(require('vue-clipboard2'));

/**
 * Next, we will create a fresh Vue application instance and attach it to
 * the page. Then, you may begin adding components to this application
 * or customize the JavaScript scaffolding to fit your unique needs.
 */

Vue.component('migration-form', require('./components/MigrationForm.vue'));
Vue.component('column', require('./components/Column.vue'));

window.bus = new Vue();

const app = new Vue({
    el: '#app',

    mounted() {
        // Trigger the dom ready event so jQuery events can
        // bind on the newly rendered virtual dom
        this.$nextTick(() => {
            $(document).trigger('domReady');
        });

    },
});