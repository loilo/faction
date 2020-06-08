@set('isActive', $scope === $targetScope)

<li class="package-navigation__list-item">
  @if ($isActive)
    <span class="package-navigation__link package-navigation__link--active">
  @else
    <a href="{{ route("package.$targetScope", [ 'name' => $package->shortName ]) }}" class="package-navigation__link" accesskey="{{ $accessKey }}" draggable="false" data-prefetch="100">
  @endif
  @if (!empty($icon))
    @include("icons.$icon")
  @endif
  {{ $label }}
  {!! $isActive ? '</span>' : '</a>' !!}
</li>