@extends('layout')

@section('title'){{ $package->shortName }} &ndash; {{ config('app.name') }}@endsection

@section('head')
<link rel="stylesheet" href="/fonts/jetbrains-mono.css">
<script src="{{ mix('/js/toast.js') }}" defer></script>
<link rel="stylesheet" href="{{ mix('/css/toast.css') }}">
<link rel="stylesheet" href="{{ mix("/css/package-$scope.css") }}">
<script>
window.lang = {!! json_encode([
  'messages' => [
    'copiedInstallCommand' => __('messages.copied_install_command'),
  ]
]) !!}
</script>
@endsection

@section('body')
<div class="wrapper wrapper--package-details">
  @if ($isArchived)
  <div class="card card--alert card--warning" role="alert">
    @lang('package.archived')
  </div>
  @endif

  <div class="quick-access">
    <div>
      <a href="{{ route('packages') }}" class="link link-button" accesskey="p" draggable="false" data-prefetch="100">
        <svg xmlns="http://www.w3.org/2000/svg" class="link-button__icon" width="24" height="24" viewBox="0 0 24 24"><path fill="none" d="M0 0h24v24H0V0z"/><path fill="currentColor" d="M20 11H7.83l5.59-5.59L12 4l-8 8 8 8 1.41-1.41L7.83 13H20v-2z"/></svg>
        @lang('package.show_all')
      </a>
    </div>
    <div>
      <button class="button button--slim only-js" accesskey="i" data-copy="{{ $package->installCommand }}" data-action="click->app#copyInstallCommand">
        <span class="button__label">
        @if ($package->hasStableRelease)
        @lang('messages.install_version_label', [
          'version' => "v{$package->latestStableRelease->version}"
        ])
        @elseif ($package->hasPreRelease)
        @lang('messages.install_version_label', [
          'version' => "v{$package->latestRelease->version}"
        ])
        @else
        @lang('messages.install_label')
        @endif
        </span>
        <span class="button__area">
          @include('icons.install', [ 'attributes' => [ 'class' => 'button__icon' ] ])
        </span>
      </button>
      <a href="{{ $package->githubUrl }}" class="button button--slim" accesskey="g" draggable="false">
        <span class="button__label">GitHub</span>
        <span class="button__area">
          @include('icons.github', [ 'attributes' => [ 'class' => 'button__icon' ] ])
        </span>
      </a>
    </div>
  </div>

  <div class="card card--details">
    <nav class="package-navigation">
      <ul class="package-navigation__list">
        @if ($hasReadme)
        @include('package-details.navigation-button', [
          'targetScope' => 'readme',
          'label' => __('package.sections.readme'),
          'icon' => 'readme',
          'accessKey' => 'd'
        ])
        @endif
        @include('package-details.navigation-button', [
          'targetScope' => 'versions',
          'label' => __('package.sections.versions'),
          'icon' => 'versions',
          'accessKey' => 'v'
        ])
        @include('package-details.navigation-button', [
          'targetScope' => 'relations',
          'label' => __('package.sections.relations'),
          'icon' => 'relations',
          'accessKey' => 'r'
        ])
      </ul>
    </nav>

    @include("package-details.$scope")
  </div>
</div>
@endsection
