@extends('layouts.main')
@section('content')
    <login-form action="{{ route('api.auth.login') }}"></login-form>
@endsection
