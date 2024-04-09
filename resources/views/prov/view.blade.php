@extends('layouts.app')

@section('title', 'Provincia de '. $provincia->nombre )

@section('content')
     @include('prov.info')
@stop
