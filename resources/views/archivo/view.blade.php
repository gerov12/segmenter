@extends('layouts.app')

@section('title', 'Archivo '.$archivo->nombre)

@section('content')
     @include('archivo.info')
@stop
