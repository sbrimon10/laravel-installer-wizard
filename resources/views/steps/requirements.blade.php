@extends('installer-wizard::layout')

@section('title', 'Requirements')

@section('content')
<h2 class="text-xl font-semibold mb-3">Requirements</h2>

<div class="mb-4 text-sm p-3 rounded-lg {{ $checks['php']['ok'] ? 'bg-green-50 text-green-800' : 'bg-red-50 text-red-800' }}">
  PHP {{ $checks['php']['current'] }} (required >= {{ $checks['php']['required'] }})
  — {{ $checks['php']['ok'] ? 'OK' : 'FAIL' }}
</div>

<h3 class="font-semibold text-sm mb-2">Required extensions <span class="font-normal text-slate-400">(missing blocks install)</span></h3>
<ul class="text-sm mb-4 grid grid-cols-1 sm:grid-cols-2 gap-1">
@foreach($checks['required'] as $ext => $ok)
  <li class="{{ $ok ? 'text-green-700' : 'text-red-700' }}">{{ $ok ? '✓' : '✗' }} {{ $ext }}</li>
@endforeach
</ul>

@if(count($checks['optional']))
<h3 class="font-semibold text-sm mb-2">Recommended extensions <span class="font-normal text-slate-400">(missing only warns)</span></h3>
<ul class="text-sm mb-4 grid grid-cols-1 sm:grid-cols-2 gap-1">
@foreach($checks['optional'] as $ext => $ok)
  <li class="{{ $ok ? 'text-green-700' : 'text-amber-700' }}">{{ $ok ? '✓' : '! ' }} {{ $ext }}@if(!$ok) <span class="text-xs">— some features may be limited</span>@endif</li>
@endforeach
</ul>
@endif

<h3 class="font-semibold text-sm mb-2">Permissions</h3>
<ul class="text-sm mb-4">
@foreach($checks['permissions'] as $path => $p)
  <li class="{{ $p['exists'] && $p['writable'] ? 'text-green-700' : 'text-red-700' }}">
    {{ $p['exists'] && $p['writable'] ? '✓' : '✗' }} {{ $path }} ({{ $p['writable'] ? 'writable' : 'not writable' }})
  </li>
@endforeach
</ul>

<details class="mb-6 text-sm text-slate-500">
  <summary class="cursor-pointer hover:text-slate-700">Server info: {{ count($checks['loaded']) }} loaded extensions (details)</summary>
  <p class="mt-2 text-xs leading-relaxed">{{ implode(', ', $checks['loaded']) }}</p>
</details>

<div class="flex flex-col sm:flex-row gap-3">
  <a href="{{ route('installer.welcome') }}" class="px-4 py-2 rounded-lg bg-slate-200 text-center">← Back</a>
  @if($checks['passes'])
    @if(config('installer-wizard.license_enabled'))
      <a href="{{ route('installer.license') }}" class="px-4 py-2 rounded-lg bg-green-600 text-white text-center">All good — continue to License →</a>
    @else
      <a href="{{ route('installer.environment') }}" class="px-4 py-2 rounded-lg bg-green-600 text-white text-center">All good — continue to Env →</a>
    @endif
  @else
    <a href="{{ route('installer.requirements') }}" class="px-4 py-2 rounded-lg bg-red-600 text-white text-center">Fix required items above, then refresh</a>
  @endif
</div>
@endsection
