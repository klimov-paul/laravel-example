import axios from "axios";

/**
 * Provides commons properties and methods for the basic form, which should be validated and submitted via AJAX.
 *
 * In case Laravel validation error, error messages will be stored at `errors`.
 * In case 'redirect' key has been passed in success JSON response, browser location will be switched to it.
 *
 * Override `formData` data object to match particular form fields.
 * Override `success` method to provide your own response handling.
 */
export default {
    props: {
        action: {
            type: String,
            default: () => null,
        },
        method: {
            type: String,
            default: () => 'POST',
        },
    },
    data() {
        return {
            formData: {},
            errors: {},
            loading: false,
        };
    },
    methods: {
        send() {
            if (this.loading) {
                return;
            }

            this.loading = true;
            this.errors = {};

            axios
                .request({
                    method: this.method,
                    url: this.action,
                    data: this.formData
                })
                .then(res => {
                    if (res.data.redirect) {
                        window.location = res.data.redirect;
                        return;
                    }

                    if (this.success(res) === true) {
                        return;
                    }

                    this.loading = false;
                })
                .catch(error => {
                    if (this.error(error.response) === true) {
                        return;
                    }

                    if (error.response.status === 422) {
                        this.errors = error.response.data.errors;
                        this.loading = false;

                        return;
                    }

                    this.loading = false;
                    console.error(error);
                });
        },
        success(response) {
            window.location = '/';
        },
        error(response) {
            // blank
        },
    }
}
