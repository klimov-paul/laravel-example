@extends('layouts.main')
@section('content')
    @auth('web')
        <div>
            <a class="btn btn-primary" href="{{ route('me.subscriptions.purchase') }}">Purchase Subscription</a>
        </div>
    @else
        <div>
            Home
        </div>
    @endauth
@endsection
