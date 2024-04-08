@extends('layouts.app')

@section('title', 'Provincia {{ $provincia->nombre }}')

@section('content')
     @include('prov.info')
@stop
