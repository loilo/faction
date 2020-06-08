<div class="versions-headline"><h1>@lang('package.versions.headline')</h1></div>
<hr class="versions-line">
<ul class="versions-list">
@set('releases', $package->releases->reverse())
@foreach ($releases as $release)
  @set('version', $release->version)
  <li data-next="{{ (string) ($release->nextStable->version ?? '') }}" class="versions-list__item versions-list__item--type-{{ $version->type }}">
    @if (
      is_null($release->next) ||
      $release->time->isToday() ||
      $release->time->format('d.m.Y') !== $release->next->time->format('d.m.Y')
    )
    <time class="versions-list__time" title="{{ $release->time }}" datetime="{{ $release->time }}">{{ View\recency($release->time) }}</time>
    @endif
    <div aria-hidden="true" class="versions-list__marker"></div>
    <div class="versions-list__version-link">
      <a href="{{ $package->githubUrl }}/{{ Str::startsWith($version->originalString, 'dev-') ? 'tree/' : "releases/tag/$version->originalString" }}" title="@lang('package.versions.show_release')" class="link">{{ (string) $version }}</a>
    </div>
    <div class="versions-list__actions">
      <a href="{{ $package->githubUrl }}/commit/{{ $release->fullCommit }}" title="@lang('package.versions.show_commit')" class="versions-list__action versions-list__link">
        <svg aria-hidden="true" viewBox="0 0 14 16" version="1.1" width="14" height="16" aria-hidden="true"><path fill="currentColor" fill-rule="evenodd" d="M10.86 7c-.45-1.72-2-3-3.86-3-1.86 0-3.41 1.28-3.86 3H0v2h3.14c.45 1.72 2 3 3.86 3 1.86 0 3.41-1.28 3.86-3H14V7h-3.14zM7 10.2c-1.22 0-2.2-.98-2.2-2.2 0-1.22.98-2.2 2.2-2.2 1.22 0 2.2.98 2.2 2.2 0 1.22-.98 2.2-2.2 2.2z"></path></svg> <code>{{ $release->commit }}</code>
      </a>

      @if (
        !is_null($release->previousStable) &&
        $release->previousStable->commit !== $release->commit
      )
      <a href="{{ $package->githubUrl }}/compare/{{ $release->previousStable->version->ref }}..{{ $release->version->ref }}" title="@lang('package.versions.show_diff', [
        'version' => $release->previousStable->version->ref
      ])" class="versions-list__action versions-list__link">
        <svg aria-hidden="true" class="button__icon" width="13" height="16"><path fill="currentColor" d="M6 7h2v1H6v2H5V8H3V7h2V5h1v2zm-3 6h5v-1H3v1zM7.5 2L11 5.5V15c0 .55-.45 1-1 1H1c-.55 0-1-.45-1-1V3c0-.55.45-1 1-1h6.5zM10 6L7 3H1v12h9V6zM8.5 0H3v1h5l4 4v8h1V4.5L8.5 0z"></path></svg>
      </a>
      @endif
    </div>
  </li>
@endforeach
</ul>