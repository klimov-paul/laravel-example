@extends('layouts.main')
@push('after-styles')
    <script src="https://js.braintreegateway.com/web/dropin/1.43.0/js/dropin.min.js"></script>
@endpush
@section('content')
    <subscription-purchase-form
        action="{{ route('api.me.subscriptions.store') }}"
        subscription-plans-api-url="{{ route('api.subscriptionPlans.index') }}"
        braintree-generate-token-url="{{ route('api.braintree.generate-client-token') }}"
    ></subscription-purchase-form>
@endsection
