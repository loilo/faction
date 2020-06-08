@extends('layout')

@section('title')@lang('messages.overview_headline') - {{ config('app.name') }}@endsection

@section('head')
<meta name="turbolinks-cache-control" content="no-cache">
@if ($groups->isNotEmpty())
<script>
window.groups = @json($groups, JSON_UNESCAPED_SLASHES)
</script>

{{-- Interpolated way of adding style tags to avoid breaking syntax highlighting --}}
{!! '<style type="text/css">' !!}
@foreach ($groups as $group)
.package--group-{{ $group->id }} {
  --accent--lighter: {{ $group->colors->lighter }};
  --accent--light: {{ $group->colors->light }};
  --accent--dark: {{ $group->colors->dark }};
  --accent--darker: {{ $group->colors->darker }};
}
@endforeach
{!! '</style>' !!}

<script>
window.packages = @json($packages->map(function($package) {
    return $package->name;
}), JSON_UNESCAPED_SLASHES)

window.lang = {!! json_encode([
  'results' => [
    'singular' => __('messages.search.results_singular'),
    'plural' => __('messages.search.results_plural'),
  ],
  'messages' => [
    'copiedInstallCommand' => __('messages.copied_install_command'),
    'searchReady' => __('messages.search.ready')
  ],
  'error' => [
    'search' => [
      'notReady' => __('error.search.not_ready'),
      'initializationError' => __('error.search.initialization_error')
    ]
  ]
]) !!}
</script>
@endif

@endsection

@section('body')

@if ($packages->isNotEmpty())
<div data-controller="list">
  <div class="infobar">
    @include('package-list.search', [ 'query' => $searchQuery ])
    <a href="/help" class="help-button" title="@lang('messages.help_tooltip')" accesskey="h">
      <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"><path fill="none" d="M0 0h24v24H0V0z"/><path fill="currentColor" d="M11 16h2v2h-2zm1-14C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8zm0-14c-2.21 0-4 1.79-4 4h2c0-1.1.9-2 2-2s2 .9 2 2c0 2-3 1.75-3 5h2c0-2.25 3-2.5 3-5 0-2.21-1.79-4-4-4z"/></svg>
    </a>
  </div>

  {!! $unsortedPackageNames !!}
  <style data-target="list.filterStyle" type="text/css">{!! $filterStyle !!}</style>
  <ul class="packages-list{{ $packagesListClasses }}" data-target="list.packageList" data-action="keydown->list#navigate">
    @each('package-list.item', $packages, 'package')
  </ul>
</div>
@else
<p>@lang('package.none_available')</p>
@endif
@endsection
