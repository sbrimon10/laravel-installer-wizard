@extends('installer-wizard::layout')

@section('title', 'Complete')

@section('content')
<h2 class="text-xl font-semibold mb-3">Complete</h2>
@if($installed)
<p class="text-sm text-green-700 bg-green-50 p-3 rounded-lg mb-4">Application is installed (lock file exists). The wizard is now locked — set <code>INSTALLER_ENABLED=false</code> to hide it fully.</p>
<a href="{{ route('installer.welcome') }}" class="inline-block px-4 py-2 rounded-lg bg-slate-200 text-sm">← Review steps</a>
@else
<p class="text-sm text-slate-600 mb-4">Admin is ready. Finish to write <code>storage/installer.lock</code> and lock the wizard.</p>
<form method="POST" action="{{ route('installer.finish') }}" class="flex gap-3">
  @csrf
  <a href="{{ route('installer.admin') }}" class="px-4 py-2 rounded-lg bg-slate-200 text-sm">← Back</a>
  <button type="submit" class="px-5 py-2 rounded-lg bg-green-600 text-white text-sm">Finish installation</button>
</form>
@endif
@endsection
