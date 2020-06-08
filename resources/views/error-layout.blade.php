@extends('layout')

@section('head')
<style>
html,
body {
  background-color: #faf9fb;
  color: #706773;
  font-weight: 100;
  height: 100vh;
  margin: 0;
}

.full-height {
  height: 100vh;
}

.flex-center {
  align-items: center;
  display: flex;
  justify-content: center;
}

.position-ref {
  position: relative;
}

.code {
  border-right: 2px solid;
  margin-right: 0.75em;
  padding: 0.25em 0.75em;
  text-align: center;
  color: #c5c0c6;
  display: flex;
  align-items: center;
  font-weight: 500;
  font-size: 1.5em;
}

.message {
  font-size: 20px;
  text-align: center;
}
</style>
@endsection

@section('body')
<div class="flex-center position-ref full-height">
  <div class="code">
    @yield('status')
  </div>
  <div class="message">
    @yield('message')
  </div>
</div>
@endsection