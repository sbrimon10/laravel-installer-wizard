@extends('installer-wizard::layout')

@section('title', 'Database')

@section('content')
<h2 class="text-xl font-semibold mb-3">Database</h2>
<p class="text-sm text-slate-500 mb-4">Runs your app migrations with <code>--force</code>@if($seed). Seeding is enabled (<code>INSTALLER_RUN_SEED=true</code>).@endif</p>

@if(!($reachable['ok'] ?? true))
<div class="mb-4 text-sm bg-amber-50 text-amber-800 p-3 rounded-lg">
  Current <code>.env</code> connection fails: {{ $reachable['message'] ?? '' }}<br>
  <a href="{{ route('installer.environment') }}" class="underline font-medium">Fix it in the Env step</a> — the wizard stays usable.
</div>
@endif

@if($errors->any())
<div class="mb-4 text-sm bg-red-50 text-red-800 p-3 rounded-lg">
  <ul class="list-disc ml-5">
    @foreach($errors->all() as $error)
      <li>{{ $error }}</li>
    @endforeach
  </ul>
</div>
@endif

@if(session('migrate_output'))
<div class="mb-4 text-xs bg-slate-900 text-green-300 p-3 rounded-lg overflow-auto max-h-48 whitespace-pre-wrap">{{ session('migrate_output') }}</div>
@endif

<form method="POST" action="{{ route('installer.database.run') }}" class="space-y-4">
  @csrf
  <label class="flex items-start gap-2 text-sm text-slate-600">
    <input type="checkbox" name="fresh" value="1" class="mt-1">
    <span><strong>Fresh install</strong> — drop all tables first (<code>migrate:fresh</code>). Only tick on a brand-new database.</span>
  </label>
  <div class="flex gap-3">
    <a href="{{ route('installer.environment') }}" class="px-4 py-2 rounded-lg bg-slate-200 text-sm">← Back</a>
    <button type="submit" class="px-5 py-2 rounded-lg bg-slate-900 text-white text-sm">Run migrations →</button>
  </div>
</form>
@endsection
