<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>@yield('title', 'Installer') — {{ config('installer-wizard.title', 'Laravel Installer Wizard') }}</title>
<script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-100 min-h-screen">
<div class="max-w-4xl mx-auto py-6 sm:py-12 px-4">
  <div class="bg-white rounded-2xl shadow p-5 sm:p-8">
    <h1 class="text-xl sm:text-2xl font-bold mb-1">{{ config('installer-wizard.title', 'Laravel Installer Wizard') }}</h1>
    <p class="text-slate-500 text-sm mb-1">{{ config('installer-wizard.description', 'Set up your application in a few guided steps.') }}</p>
    <p class="text-slate-400 text-xs mb-6">Powered by <code>sbrimon10/laravel-installer-wizard</code></p>

    @php
      $steps = [
        ['route' => 'installer.welcome', 'label' => '1. Welcome'],
        ['route' => 'installer.requirements', 'label' => '2. Requirements'],
        ['route' => 'installer.environment', 'label' => '3. Env'],
        ['route' => 'installer.database', 'label' => '4. DB'],
        ['route' => 'installer.admin', 'label' => '5. Admin'],
        ['route' => 'installer.complete', 'label' => '6. Done'],
      ];
      $currentIndex = collect($steps)->search(fn ($s) => request()->routeIs($s['route']));
      $currentIndex = $currentIndex === false ? 0 : $currentIndex;
    @endphp
    <nav class="flex flex-wrap gap-2 text-xs mb-6" aria-label="Installer steps">
      @foreach($steps as $i => $step)
        @if($i < $currentIndex)
          <a href="{{ route($step['route']) }}" class="px-3 py-1 rounded-full bg-green-100 text-green-800 whitespace-nowrap">✓ {{ $step['label'] }}</a>
        @elseif($i === $currentIndex)
          <span class="px-3 py-1 rounded-full bg-slate-900 text-white whitespace-nowrap">{{ $step['label'] }}</span>
        @else
          <a href="{{ route($step['route']) }}" class="px-3 py-1 rounded-full bg-slate-200 text-slate-600 whitespace-nowrap">{{ $step['label'] }}</a>
        @endif
      @endforeach
    </nav>

    @if(session('status'))
    <div class="mb-4 text-sm bg-green-50 text-green-800 p-3 rounded-lg">{{ session('status') }}</div>
    @endif
    @yield('content')
  </div>
  <p class="text-center text-xs text-slate-400 mt-4">Delete or protect /install after <code>storage/installer.lock</code> exists.</p>
</div>
</body>
</html>
