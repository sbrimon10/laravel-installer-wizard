@extends('installer-wizard::layout')

@section('title', 'Welcome')

@section('content')
<h2 class="text-xl font-semibold mb-3">Welcome</h2>
<p class="text-slate-600 mb-4">This wizard will check requirements, configure <code>.env</code>, run migrations and create your admin user.</p>
<ul class="list-disc ml-5 text-sm text-slate-600 mb-6">
  <li>Laravel 12 + 13, PHP ^8.3</li>
  <li>License step is optional (off by default)</li>
  <li>Tailwind via CDN for skeleton — publish views to customize</li>
</ul>
<a href="{{ route('installer.requirements') }}" class="inline-block bg-slate-900 text-white px-5 py-2 rounded-lg">Check requirements →</a>
@endsection
