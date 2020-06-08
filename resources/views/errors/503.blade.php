@extends('error-layout')

@section('title')@lang('error.maintenance.title') - {{ config('app.name') }}@endsection

@section('head')
@parent

<style>
.loader {
  --edge-blocks: 4;
  --block-size: 6px;
  --block-margin: 2px;
  --block-outer-size: calc(var(--block-size) + var(--block-margin));
  --loader-size: calc(
    var(--block-size) * var(--edge-blocks) + var(--block-margin) *
      (var(--edge-blocks) - 1)
  );

  height: var(--loader-size);
  width: var(--loader-size);
  display: inline-block;
  position: relative;
  margin-bottom: -2px;
  margin-right: 0.25em;
}
.loader span {
  background: currentColor;
  display: block;
  height: var(--block-size);
  opacity: 0;
  position: absolute;
  width: var(--block-size);
  animation: load 3.4s ease-in-out infinite;
}
.loader span.block-1 {
  animation-delay: 0.786s;
  left: 0px;
  top: 0px;
}
.loader span.block-2 {
  animation-delay: 0.712s;
  left: var(--block-outer-size);
  top: 0px;
}
.loader span.block-3 {
  animation-delay: 0.648s;
  left: calc(2 * var(--block-outer-size));
  top: 0px;
}
.loader span.block-4 {
  animation-delay: 0.574s;
  left: calc(3 * var(--block-outer-size));
  top: 0px;
}
.loader span.block-5 {
  animation-delay: 0.51s;
  left: 0px;
  top: var(--block-outer-size);
}
.loader span.block-6 {
  animation-delay: 0.446s;
  left: var(--block-outer-size);
  top: var(--block-outer-size);
}
.loader span.block-7 {
  animation-delay: 0.372s;
  left: calc(2 * var(--block-outer-size));
  top: var(--block-outer-size);
}
.loader span.block-8 {
  animation-delay: 0.308s;
  left: calc(3 * var(--block-outer-size));
  top: var(--block-outer-size);
}
.loader span.block-9 {
  animation-delay: 0.234s;
  left: 0px;
  top: calc(2 * var(--block-outer-size));
}
.loader span.block-10 {
  animation-delay: 0.17s;
  left: var(--block-outer-size);
  top: calc(2 * var(--block-outer-size));
}
.loader span.block-11 {
  animation-delay: 0.106s;
  left: calc(2 * var(--block-outer-size));
  top: calc(2 * var(--block-outer-size));
}
.loader span.block-12 {
  animation-delay: 0.032s;
  left: calc(3 * var(--block-outer-size));
  top: calc(2 * var(--block-outer-size));
}
.loader span.block-13 {
  animation-delay: -0.032s;
  left: 0px;
  top: calc(3 * var(--block-outer-size));
}
.loader span.block-14 {
  animation-delay: -0.106s;
  left: var(--block-outer-size);
  top: calc(3 * var(--block-outer-size));
}
.loader span.block-15 {
  animation-delay: -0.17s;
  left: calc(2 * var(--block-outer-size));
  top: calc(3 * var(--block-outer-size));
}
.loader span.block-16 {
  animation-delay: -0.234s;
  left: calc(3 * var(--block-outer-size));
  top: calc(3 * var(--block-outer-size));
}

@keyframes load {
  0% {
    opacity: 0;
    transform: translateY(calc(-1.5 * var(--loader-size)));
  }
  15% {
    opacity: 0;
    transform: translateY(calc(-1.5 * var(--loader-size)));
  }
  30% {
    opacity: 1;
    transform: translateY(0);
  }
  70% {
    opacity: 1;
    transform: translateY(0);
  }
  85% {
    opacity: 0;
    transform: translateY(calc(1.5 * var(--loader-size)));
  }
  100% {
    opacity: 0;
    transform: translateY(calc(1.5 * var(--loader-size)));
  }
}
</style>
@endsection

@section('status')
<div class="loader">
  <span class="block-1"></span>
  <span class="block-2"></span>
  <span class="block-3"></span>
  <span class="block-4"></span>
  <span class="block-5"></span>
  <span class="block-6"></span>
  <span class="block-7"></span>
  <span class="block-8"></span>
  <span class="block-9"></span>
  <span class="block-10"></span>
  <span class="block-11"></span>
  <span class="block-12"></span>
  <span class="block-13"></span>
  <span class="block-14"></span>
  <span class="block-15"></span>
  <span class="block-16"></span>
</div>
@endsection

@section('message')
@lang('error.maintenance.message')
@endsection