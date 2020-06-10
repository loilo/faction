<li
    class="package package--group-{{ $package->group->id }}"
    data-package="{{ $package->shortName }}"
    data-full-package="{{ $package->name }}"
    data-target="list.package"
    >
    <a class="package__link" href="{{ route('package', [ 'name' => $package->shortName ]) }}" data-target="list.packageLink" data-prefetch="150">
    <div class="package__name">{!! View\decoratePackageName($package->shortName) !!}</div>
    @if ($package->hasStableRelease)
    <div class="package__version">{{ $package->latestStableRelease->version }}</div>
    @elseif ($package->hasPreRelease)
    <div class="package__version">{{ $package->latestRelease->version }}</div>
    @endif
    <div class="package__updated">
        <img src="/img/groups/{{ $package->group->id }}/history.svg" alt="" class="package__updated-icon">
        {{--
          Don't change the class of the following element without considering
          what the App\Library\ResponseCache\LatestChangeReplacer class does.
        --}}
        <time class="package__updated-time" datetime="{{ $package->lastModified->toIso8601String() }}">{{ $package->lastModified->diffForHumans() }}</time>

        {{--
          This adds additional note when the last modification
          does *not* equal the latest stable release:
        --}}
        @if ($package->hasStableRelease)
          @if ($package->lastModified > $package->latestStableRelease->time)
            @if ($package->lastModified > $package->latestRelease->time)
            <div class="package__last-modified-ref">
              @lang('package.latest_commit_on_branch')
              <img src="/img/groups/{{ $package->group->id }}/branch.svg" class="package__branch-icon">
              <code class="package__branch">{{ Str::substr($package->head->version, 4) }}</code>
            </div>
            @else
            <div class="package__last-modified-ref">
            {{ $package->latestRelease->version }}
            </div>
            @endif
          @endif
        @elseif ($package->hasPreRelease)
          @if ($package->lastModified > $package->latestRelease->time)
          <div class="package__last-modified-ref">
            @lang('package.latest_commit_on_branch')
            <img src="/img/groups/{{ $package->group->id }}/branch.svg" class="package__branch-icon">
            <code class="package__branch">{{ Str::substr($package->head->version, 4) }}</code>
          </div>
          @endif
        @endif
    </div>
    </a>
    <div class="package__options">
    <button title="@lang('package.copy_install')" type="button" class="package__action-button only-js" data-copy="{{ $package->installCommand }}" data-action="click->app#copyInstallCommand">
        <img draggable="false" src="/img/groups/{{ $package->group->id }}/install.svg">
    </button>
    <a title="@lang('package.open_in_github')" href="{{ $package->githubUrl }}" class="package__action-button">
      <img draggable="false" src="/img/groups/{{ $package->group->id }}/github.svg">
    </a>
    </div>
</li>