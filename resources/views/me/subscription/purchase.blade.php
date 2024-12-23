@extends('layouts.main')
@section('content')
    <subscription-purchase-form
        action="{{ route('api.me.subscriptions.store') }}"
        subscription-plans-api-url="{{ route('api.subscriptionPlans.index') }}"
        braintree-generate-token-url="{{ route('api.braintree.generate-client-token') }}"
    ></subscription-purchase-form>
@endsection
