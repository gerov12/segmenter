@extends('layouts.app')

@section('title', 'Entidad '.$entidad->nombre)

@section('content')
     @include('entidad.info')
@stop
