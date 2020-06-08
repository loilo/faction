@extends('error-layout')

@section('title')@lang('error.not_found.title') - {{ config('app.name') }}@endsection

@section('status')
404
@endsection

@section('message')
@lang('error.not_found.message')
@endsection