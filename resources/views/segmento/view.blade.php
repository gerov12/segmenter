@extends('layouts.app')

@section('title', 'Segmento '.$segmento->id)

@section('content')
     @include('segmento.info')
@stop
