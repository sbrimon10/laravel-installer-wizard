<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>@yield('title', 'Installer') — Laravel Installer Wizard</title>
<script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-100 min-h-screen">
<div class="max-w-2xl mx-auto py-12 px-4">
  <div class="bg-white rounded-2xl shadow p-8">
    <h1 class="text-2xl font-bold mb-2">Laravel Installer Wizard</h1>
    <p class="text-slate-500 text-sm mb-6">sbrimon10/laravel-installer-wizard · v0.0.1 skeleton</p>
    <div class="flex gap-2 text-xs mb-6">
      <a href="{{ route('installer.welcome') }}" class="px-3 py-1 rounded-full bg-slate-900 text-white">1. Welcome</a>
      <a href="{{ route('installer.requirements') }}" class="px-3 py-1 rounded-full bg-slate-200">2. Requirements</a>
      <a href="{{ route('installer.environment') }}" class="px-3 py-1 rounded-full bg-slate-200">3. Env</a>
      <a href="{{ route('installer.database') }}" class="px-3 py-1 rounded-full bg-slate-200">4. DB</a>
      <a href="{{ route('installer.admin') }}" class="px-3 py-1 rounded-full bg-slate-200">5. Admin</a>
    </div>
    @if(session('status'))
    <div class="mb-4 text-sm bg-green-50 text-green-800 p-3 rounded-lg">{{ session('status') }}</div>
    @endif
    @yield('content')
  </div>
  <p class="text-center text-xs text-slate-400 mt-4">Delete or protect /install after <code>storage/installer.lock</code> exists.</p>
</div>
</body>
</html>
