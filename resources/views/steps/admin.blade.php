@extends('installer-wizard::layout')

@section('title', 'Admin')

@section('content')
<h2 class="text-xl font-semibold mb-3">Admin user</h2>
<p class="text-sm text-slate-500 mb-4">Creates (or updates) the admin account on your <code>users</code> table. Existing email gets its name + password reset.</p>

@if($errors->any())
<div class="mb-4 text-sm bg-red-50 text-red-800 p-3 rounded-lg">
  <ul class="list-disc ml-5">
    @foreach($errors->all() as $error)
      <li>{{ $error }}</li>
    @endforeach
  </ul>
</div>
@endif

<form method="POST" action="{{ route('installer.admin.store') }}" class="space-y-4">
  @csrf
  <div>
    <label class="block text-sm font-medium mb-1" for="name">Name</label>
    <input id="name" name="name" value="{{ old('name') }}" required
      class="w-full border rounded-lg px-3 py-2 text-sm" placeholder="Site Admin">
  </div>
  <div>
    <label class="block text-sm font-medium mb-1" for="email">Email</label>
    <input id="email" name="email" type="email" value="{{ old('email') }}" required
      class="w-full border rounded-lg px-3 py-2 text-sm" placeholder="admin@example.com">
  </div>
  <div class="grid grid-cols-2 gap-4">
    <div>
      <label class="block text-sm font-medium mb-1" for="password">Password (min 8)</label>
      <input id="password" name="password" type="password" required
        class="w-full border rounded-lg px-3 py-2 text-sm" autocomplete="new-password">
    </div>
    <div>
      <label class="block text-sm font-medium mb-1" for="password_confirmation">Confirm</label>
      <input id="password_confirmation" name="password_confirmation" type="password" required
        class="w-full border rounded-lg px-3 py-2 text-sm" autocomplete="new-password">
    </div>
  </div>
  <div class="flex gap-3">
    <a href="{{ route('installer.database') }}" class="px-4 py-2 rounded-lg bg-slate-200 text-sm">← Back</a>
    <button type="submit" class="px-5 py-2 rounded-lg bg-slate-900 text-white text-sm">Create admin →</button>
  </div>
</form>
@endsection
