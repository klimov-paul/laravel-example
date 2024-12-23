<template>
    <form :method="method" :action="action" @submit.prevent="send()">
        <div class="loading" v-if="loading"></div>

        <div class="mb-3">
            <table class="table table-bordered">
                <thead class="thead-dark">
                    <tr>
                        <th></th>
                        <th>Plan</th>
                        <th>Max rents</th>
                        <th>Max book price</th>
                        <th>Price</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="(subscriptionPlan, i) in subscriptionPlans">
                        <td>
                            <input type="radio" v-model="formData.subscription_plan_id" required :value="subscriptionPlan.id">
                        </td>
                        <td>
                            {{ subscriptionPlan.name }}
                        </td>
                        <td>
                            {{ subscriptionPlan.max_rent_count }}
                        </td>
                        <td>
                            ${{ subscriptionPlan.max_book_price }}
                        </td>
                        <td>
                            <b>${{ subscriptionPlan.max_book_price }}</b>
                        </td>
                    </tr>
                </tbody>
            </table>
            <span v-if="errors.subscription_plan_id" class="invalid-feedback" role="alert">
                <strong>{{ errors.subscription_plan_id[0] }}</strong>
            </span>
        </div>
        <div class="mb-3">
            <div id="braintree-dropin-container"></div>
        </div>
        <div class="mb-3 form-check">
            <input type="checkbox" class="form-check-input" name="accept_terms" id="input-accept_terms" v-model="formData.accept_terms">
            <label class="form-check-label" for="input-accept_terms">I accept subscription terms</label>
            <span v-if="errors.accept_terms" class="invalid-feedback" role="alert">
                <strong>{{ errors.accept_terms[0] }}</strong>
            </span>
        </div>
        <button type="submit" class="btn btn-primary">Purchase</button>
    </form>
</template>
<script>
import AjaxForm from '@/mixins/AjaxForm';
import axios from 'axios';

export default {
    name: "SubscriptionPurchaseForm",
    mixins: [
        AjaxForm,
    ],
    props: {
        subscriptionPlansApiUrl: {
            type: String,
            default: () => null,
        },
        braintreeGenerateTokenUrl: {
            type: String,
            default: () => null,
        },
    },
    data() {
        return {
            subscriptionPlans: [],
            formData: {
                subscription_plan_id: '',
                token: '',
                accept_terms: false,
            },
        };
    },
    created() {
        this.getSubscriptionPlans();
        this.initBraintreeDropin();
    },
    methods: {
        async getSubscriptionPlans() {
            this.loading = true;

            const response = await axios.get(
                this.subscriptionPlansApiUrl
            );

            this.subscriptionPlans = response.data.data;

            this.loading = false;
        },
        async initBraintreeDropin() {
            this.loading = true;

            const response = await axios.post(
                this.braintreeGenerateTokenUrl
            );

            let clientToken = response.data.data.token;

            braintree.dropin.create({
                // Step three: get client token from your server, such as via
                //    templates or async http request
                authorization: clientToken,
                container: '#braintree-dropin-container'
            }).then((dropinInstance) => {
                // Use 'dropinInstance' here
                // Methods documented at https://braintree.github.io/braintree-web-drop-in/docs/current/Dropin.html
            }).catch((error) => {
                console.error(error);
            });

            this.loading = false;
        },
        success(response) {
            window.location = '/';
        },
    }
}
</script>
