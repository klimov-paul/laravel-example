import './bootstrap';

import 'bootstrap/js/src/dropdown';

import { createApp } from 'vue';

import LoginForm from './components/Auth/LoginForm.vue';
import SubscriptionPurchaseForm from './components/Me/Subscription/SubscriptionPurchaseForm.vue';

/*Vue.filter('priceFormat', function(value) {
    if (!value) return '--';
    return `${(+value).toFixed(2)}$`;
});*/

createApp({
    components: {
        LoginForm,
        SubscriptionPurchaseForm,
    }
}).mount("#app");
