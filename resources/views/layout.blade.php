<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <title>@section('title'){{ config('app.name') }}@show</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <script src="{{ mix('/js/app.js') }}" defer></script>
  <link rel="stylesheet" href="{{ mix('/css/modern-normalize.css') }}">
  <link rel="preload" href="/fonts/quicksand-light.woff2" as="font" type="font/woff2" crossorigin="anonymous">
  <link rel="preload" href="/fonts/quicksand-regular.woff2" as="font" type="font/woff2" crossorigin="anonymous">
  <link rel="preload" href="/fonts/quicksand-medium.woff2" as="font" type="font/woff2" crossorigin="anonymous">
  <link rel="stylesheet" href="/fonts/quicksand.css">
  <link rel="stylesheet" href="{{ mix('/css/stylesheet.css') }}">
  @buffer
  @include('icons.relations', [ 'fill' => '#ad63c7' ])
  @endbuffer('icon')
  <link rel="icon" type="image/svg+xml" href="data:image/svg+xml;base64,{{ base64_encode($icon) }}" sizes="any">

  @yield('head')
</head>

<body class="no-js" data-target="app.container" data-controller="app">
@yield('body')
</body>
</html>
