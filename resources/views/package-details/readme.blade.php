@if ($hasReadme)
<div class="markdown-body">
  {!! $readme !!}
</div>
@else
<div class="no-data">
  @lang('package.no_readme')
</div>
@endif