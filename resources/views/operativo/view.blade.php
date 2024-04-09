@extends('layouts.app')

@section('title', 'Operativo '. $operativo->nombre )

@section('content')
     @include('operativo.info')
@stop
