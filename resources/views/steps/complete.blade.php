@extends('installer-wizard::layout')

@section('title', 'Complete')

@section('content')
<h2 class="text-xl font-semibold mb-3">Complete</h2>
@if($installed)
<p class="text-sm text-green-700 bg-green-50 p-3 rounded-lg">Application is installed (lock file exists).</p>
@else
<p class="text-sm text-slate-600">Wizard skeleton is working. Next: environment, database + admin steps will create <code>storage/installer.lock</code> here.</p>
@endif
<a href="{{ route('installer.welcome') }}" class="inline-block mt-4 px-4 py-2 rounded-lg bg-slate-200">← Start over</a>
@endsection
