<template>
    <form :method="method" :action="action" @submit.prevent="send()">
        <div class="loading" v-if="loading"></div>
        <div class="mb-3">
            <label for="login-input-email" class="form-label">Email address</label>
            <input type="email" class="form-control" name="email" id="login-input-email" aria-describedby="emailHelp" :class="{error: errors.email}" placeholder="Your email" v-model="formData.email" required autofocus/>
            <span v-if="errors.email" class="invalid-feedback" role="alert">
                <strong>{{ errors.email[0] }}</strong>
            </span>

            <div id="emailHelp" class="form-text">We'll never share your email with anyone else.</div>
        </div>
        <div class="mb-3">
            <label for="login-input-password" class="form-label">Password</label>
            <input type="password" class="form-control" name="password" id="login-input-password" :class="{error: errors.password}" placeholder="password" v-model="formData.password" required/>

            <span v-if="errors.password" class="invalid-feedback" role="alert">
                    <strong>{{ errors.password[0] }}</strong>
                </span>
        </div>
        <div class="mb-3 form-check">
            <input type="checkbox" class="form-check-input" name="remember" id="login-input-remember" v-model="formData.remember">
            <label class="form-check-label" for="login-input-remember">Remember me</label>
        </div>
        <button type="submit" class="btn btn-primary">Login</button>
    </form>
</template>
<script>
import AjaxForm from '@/mixins/AjaxForm';

export default {
    name: 'LoginForm',
    mixins: [
        AjaxForm,
    ],
    data() {
        return {
            formData: {
                email: '',
                password: '',
                remember: false,
            },
        };
    },
    methods: {
        success(response) {
            window.location = '/';
        },
    }
};
</script>
