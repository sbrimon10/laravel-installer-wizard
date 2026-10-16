@extends('installer-wizard::layout')

@section('title', 'License')

@section('content')
<h2 class="text-xl font-semibold mb-3">License</h2>
<p class="text-sm text-slate-500 mb-4">Enter your purchase code. It is saved to <code>.env</code> as <code>{{ config('installer-wizard.license_env_key', 'APP_PURCHASE_CODE') }}</code>.</p>

@if($errors->any())
<div class="mb-4 text-sm bg-red-50 text-red-800 p-3 rounded-lg">
  <ul class="list-disc ml-5">
    @foreach($errors->all() as $error)
      <li>{{ $error }}</li>
    @endforeach
  </ul>
</div>
@endif

<form method="POST" action="{{ route('installer.license.verify') }}" class="space-y-4">
  @csrf
  <div>
    <label class="block text-sm font-medium mb-1" for="license_code">Purchase code</label>
    <input id="license_code" name="license_code" value="{{ old('license_code', session('installer.license_code')) }}" required
      class="w-full border rounded-lg px-3 py-2 text-sm font-mono" placeholder="xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx" autocomplete="off">
  </div>
  <div class="flex flex-col sm:flex-row gap-3">
    <a href="{{ route('installer.requirements') }}" class="px-4 py-2 rounded-lg bg-slate-200 text-center">← Back</a>
    <button type="submit" class="px-5 py-2 rounded-lg bg-slate-900 text-white text-sm">Verify & continue →</button>
  </div>
</form>
@endsection
