@extends('layouts.main')
@section('content')
    @auth('web')
        @if (auth()->user()->activeSubscription)
        <div>
            Current subscription plan: "{{ auth()->user()->activeSubscription->subscriptionPlan->name }}"
        </div>
        @endif
        <div>
            <a class="btn btn-primary" href="{{ route('me.subscriptions.purchase') }}">Purchase Subscription</a>
        </div>
    @else
        <div>
            Home
        </div>
    @endauth
@endsection
