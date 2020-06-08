@extends('layout')

@section('title')@lang('messages.help_title') - {{ config('app.name') }}@endsection

@section('head')
<link rel="stylesheet" href="/fonts/jetbrains-mono.css">
@endsection

@section('body')
<div class="wrapper">
  <div class="quick-access">
    <div>
      <a href="{{ route('packages') }}" class="link link-button" accesskey="p" draggable="false" data-prefetch="100">
        <svg xmlns="http://www.w3.org/2000/svg" class="link-button__icon" width="24" height="24" viewBox="0 0 24 24"><path fill="none" d="M0 0h24v24H0V0z"/><path fill="currentColor" d="M20 11H7.83l5.59-5.59L12 4l-8 8 8 8 1.41-1.41L7.83 13H20v-2z"/></svg>
        @lang('package.show_packages')
      </a>
    </div>
  </div>

  <div class="card">
    @if (config('app.locale') !== 'en')
    <div class="card card--alert card--warning" role="alert">
      @lang('messages.help_warning')
    </div>
    @endif

    <h2>Initialize</h2>
    <p>To make packages from this site available to your project, add the following data to the <code class="inline">repositories</code> field of your <code class="inline">composer.json</code>:</p>
    <div data-action="click->app#selectOnClick">
        @code('json')
        {
            "type": "composer",
            "url": "{{ config('app.url') }}"
        }
        @endcode
    </div>

    <p>Or just run from the command line:</p>
    <div data-action="click->app#selectOnClick">
        @code('bash')
        composer config repositories.package:{{ config('app.repository.package_vendor') }}-composer composer {{ config('app.url') }}
        @endcode
    </div>

    <h2>Shortcuts</h2>
    <p>Love keyboard shortcuts? Here are some:</p>
    <strong>Note:</strong> Single letter shortcuts are HTML access keys and need additional, browser-specific <a href="https://en.wikipedia.org/wiki/Access_key#Access_in_different_browsers" class="link">modifier keys</a> to be used with.<br><kbd>⌘<hr>Ctrl</kbd> refers to the primary modifier key — <kbd>⌘</kbd> on macOS resp. <kbd>Ctrl</kbd> on Windows and Linux</p>.

    <section class="keyboard-shortcuts">
      <h3>Overview Page</h3>
      <table class="table">
        <tr>
          <th><kbd>…</kbd></th>
          <td>Just start typing to search for a package</td>
        </tr>
        <tr>
          <th><kbd>⌘<hr>Ctrl</kbd><kbd>F</kbd></th>
          <td>Focus search bar</td>
        </tr>
        <tr>
          <th><kbd>↑</kbd><kbd>↓</kbd><kbd>←</kbd><kbd>→</kbd></th>
          <td>
            Navigate through packages<br>
            Use with <kbd>⌘<hr>Ctrl</kbd> to jump to first/last item in row/column
          </td>
        </tr>
        <tr>
          <th><kbd>⌘<hr>Ctrl</kbd><kbd>C</kbd></th>
          <td>Copy the install command for the currently selected package</td>
        </tr>
        <tr>
          <th><kbd>Enter</kbd></th>
          <td>Open the selected search result item resp. open the first result if focus is on the search bar</td>
        </tr>
        <tr>
          <th><kbd>Esc</kbd></th>
          <td>Cancel search</td>
        </tr>
        <tr>
          <th><kbd>H</kbd></th>
          <td>Go to "Help" page</td>
        </tr>
      </table>
    </section>
    <section class="keyboard-shortcuts">
      <h3>Package Details Page</h3>
      <table class="table">
        <tr>
          <th><kbd>I</kbd></th>
          <td>Copy install command for package</td>
        </tr>
        <tr>
          <th><kbd>G</kbd></th>
          <td>Go to the package's GitHub repo</td>
        </tr>
        <tr>
          <th><kbd>D</kbd></th>
          <td>Go to package readme</td>
        </tr>
        <tr>
          <th><kbd>V</kbd></th>
          <td>Go to package versions</td>
        </tr>
        <tr>
          <th><kbd>R</kbd></th>
          <td>Go to package relations</td>
        </tr>
        <tr>
          <th><kbd>P</kbd></th>
          <td>Go to packages overview</td>
        </tr>
      </table>
    </section>
    <section class="keyboard-shortcuts">
      <h3>Help Page</h3>
      <table class="table">
        <tr>
          <th><kbd>P</kbd></th>
          <td>Go to packages overview</td>
        </tr>
      </table>
    </section>
    <section>
      <h2>Source</h2>
      <p>This Composer repository is run by the <strong>Faction</strong> repository generator. Faction's source code can be found <a href="https://github.com/loilo/faction" class="link">on GitHub</a>.</p>
    </section>
  </div>
</div>
@endsection
