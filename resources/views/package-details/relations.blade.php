<h1>@lang('package.relations.headline')</h1>
@if ($package->allDependencies->isEmpty() && $package->dependants->isEmpty())
<p>@lang('package.relations.no_relations')</p>
@elseif ($package->allDependencies->isEmpty())
  <h2>@lang('package.relations.dependencies.headline')</h2>
  <p>@lang('package.relations.no_dependencies')</p>
@endif

@if ($package->dependencies->isNotEmpty())
<h2>@lang('package.relations.dependencies.headline')</h2>
<table class="dependency-list">
  <thead>
    <th>@lang('package.relations.dependencies.package')</th>
    <th></th>
    <th>@lang('package.relations.dependencies.constraint')</th>
  </thead>
  @foreach ($package->dependencies as $dependency)
  @set('link', View\linkifyPackage($dependency->name))
  <tr>
    <td>
      @if (is_null($link))
      <span class="dependency-list__unlinked-dependency">{{ $dependency->name }}</span>
      @else
      <a href="{{ $link }}" class="link">{{ $dependency->name }}</a>
      @endif
    </td>
    <td class="dependency-list__separator">:</td>
    <td>{{ $dependency->constraint }}</td>
  </tr>
  @endforeach
</table>
@endif

@if ($package->devDependencies->isNotEmpty())
<h2>@lang('package.relations.dev_dependencies.headline')</h2>
<table class="dependency-list">
  <thead>
    <th>@lang('package.relations.dev_dependencies.package')</th>
    <th></th>
    <th>@lang('package.relations.dev_dependencies.constraint')</th>
  </thead>
  @foreach ($package->devDependencies as $dependency)
  @set('link', View\linkifyPackage($dependency->name))
  <tr>
    <td>
      @if (is_null($link))
      <span class="dependency-list__unlinked-dependency">{{ $dependency->name }}</span>
      @else
      <a href="{{ $link }}" class="link">{{ $dependency->name }}</a>
      @endif
    </td>
    <td class="dependency-list__separator">:</td>
    <td>{{ $dependency->constraint }}</td>
  </tr>
  @endforeach
</table>
@endif

@if ($package->dependants->isNotEmpty())
<h2>@lang('package.relations.dependants.headline')</h2>
<p>@lang('package.relations.dependants.intro', [
  'package_name' => $package->name
])</p>
<table class="dependency-list">
  <thead>
    <th>@lang('package.relations.dependants.package')</th>
    <th></th>
    <th>@lang('package.relations.dependants.version', [
      'package_name' => $package->shortName
    ])</th>
  </thead>
  <tbody>
    @foreach ($package->dependants as $dependant)
    <tr>
      @if (is_object($dependant->dependentPackage))
      <td>
        <a href="{{ View\linkifyPackage($dependant->dependentPackage->name) }}" class="link">{{ $dependant->dependentPackage->shortName }}</a>
      </td>
      <td class="dependency-list__separator">:</td>
      <td>{{ $dependant->constraint }}</td>
      @else
      <td>
        <a href="{{ View\linkifyPackage($dependant->dependentPackage) }}" class="link">{{ $dependant->dependentPackage->shortName }}</a>
      </td>
      <td class="dependency-list__separator">:</td>
      <td>{{ $dependant->constraint }}</td>
      @endif
    </tr>
    @endforeach
  </tbody>
</table>
@endif