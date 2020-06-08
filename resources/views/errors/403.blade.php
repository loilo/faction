@extends('error-layout')

@section('title')@lang('error.forbidden.title') - {{ config('app.name') }}@endsection

@section('status')
403
@endsection

@section('message')
@lang('error.forbidden.message')
@endsection