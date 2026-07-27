@extends('website.layouts.layouts')

@section('content')
<main>
@include('website.components.hero')
@include('website.components.about')
@include('website.components.newsletter')
@include('website.components.membership')
@include('website.components.events')
@include('website.components.contact')
    
</main>
@endsection