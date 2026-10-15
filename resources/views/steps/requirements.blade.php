@extends('installer-wizard::layout')

@section('title', 'Requirements')

@section('content')
<h2 class="text-xl font-semibold mb-3">Requirements</h2>

<div class="mb-4 text-sm p-3 rounded-lg {{ $checks['php']['ok'] ? 'bg-green-50 text-green-800' : 'bg-red-50 text-red-800' }}">
  PHP {{ $checks['php']['current'] }} (required >= {{ $checks['php']['required'] }})
  — {{ $checks['php']['ok'] ? 'OK' : 'FAIL' }}
</div>

<h3 class="font-semibold text-sm mb-2">Extensions</h3>
<ul class="text-sm mb-4">
@foreach($checks['extensions'] as $ext => $ok)
  <li class="{{ $ok ? 'text-green-700' : 'text-red-700' }}">{{ $ok ? '✓' : '✗' }} {{ $ext }}</li>
@endforeach
</ul>

<h3 class="font-semibold text-sm mb-2">Permissions</h3>
<ul class="text-sm mb-6">
@foreach($checks['permissions'] as $path => $p)
  <li class="{{ $p['exists'] && $p['writable'] ? 'text-green-700' : 'text-red-700' }}">
    {{ $p['exists'] && $p['writable'] ? '✓' : '✗' }} {{ $path }} ({{ $p['writable'] ? 'writable' : 'not writable' }})
  </li>
@endforeach
</ul>

<div class="flex gap-3">
  <a href="{{ route('installer.welcome') }}" class="px-4 py-2 rounded-lg bg-slate-200">← Back</a>
  @if($checks['passes'])
    <span class="px-4 py-2 rounded-lg bg-green-600 text-white">All good — Env step coming next</span>
  @else
    <span class="px-4 py-2 rounded-lg bg-red-600 text-white">Fix items above, then refresh</span>
  @endif
</div>
@endsection
