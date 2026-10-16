@extends('installer-wizard::layout')

@section('title', 'Environment')

@section('content')
<h2 class="text-xl font-semibold mb-3">Environment</h2>
<p class="text-sm text-slate-500 mb-4">Writes to your <code>.env</code> and tests the database connection before saving.</p>

@if($errors->any())
<div class="mb-4 text-sm bg-red-50 text-red-800 p-3 rounded-lg">
  <ul class="list-disc ml-5">
    @foreach($errors->all() as $error)
      <li>{{ $error }}</li>
    @endforeach
  </ul>
</div>
@endif

<form method="POST" action="{{ route('installer.environment.save') }}" class="space-y-4">
  @csrf

  <div class="grid grid-cols-1 gap-4">
    <div>
      <label class="block text-sm font-medium mb-1" for="APP_NAME">App name</label>
      <input id="APP_NAME" name="APP_NAME" value="{{ old('APP_NAME', $values['APP_NAME'] ?? '') }}" required
        class="w-full border rounded-lg px-3 py-2 text-sm" placeholder="My Application">
    </div>
    <div>
      <label class="block text-sm font-medium mb-1" for="APP_URL">App URL</label>
      <input id="APP_URL" name="APP_URL" value="{{ old('APP_URL', $values['APP_URL'] ?? '') }}" required
        class="w-full border rounded-lg px-3 py-2 text-sm" placeholder="http://localhost">
    </div>
  </div>

  <hr class="my-2">
  <h3 class="font-semibold text-sm">Database</h3>

  <div class="grid grid-cols-2 gap-4">
    <div>
      <label class="block text-sm font-medium mb-1" for="DB_CONNECTION">Connection</label>
      <select id="DB_CONNECTION" name="DB_CONNECTION" class="w-full border rounded-lg px-3 py-2 text-sm">
        @foreach(['mysql' => 'MySQL / MariaDB', 'pgsql' => 'PostgreSQL', 'sqlite' => 'SQLite'] as $val => $label)
          <option value="{{ $val }}" @selected(old('DB_CONNECTION', $values['DB_CONNECTION'] ?? 'mysql') === $val)>{{ $label }}</option>
        @endforeach
      </select>
    </div>
    <div id="row_DB_PORT">
      <label class="block text-sm font-medium mb-1" for="DB_PORT">Port <span class="text-slate-400">(optional)</span></label>
      <input id="DB_PORT" name="DB_PORT" value="{{ old('DB_PORT', $values['DB_PORT'] ?? '') }}"
        class="w-full border rounded-lg px-3 py-2 text-sm" placeholder="3306">
    </div>
    <div id="row_DB_HOST">
      <label class="block text-sm font-medium mb-1" for="DB_HOST">Host</label>
      <input id="DB_HOST" name="DB_HOST" value="{{ old('DB_HOST', $values['DB_HOST'] ?? '127.0.0.1') }}"
        class="w-full border rounded-lg px-3 py-2 text-sm" placeholder="127.0.0.1">
    </div>
    <div>
      <label class="block text-sm font-medium mb-1" for="DB_DATABASE">Database <span id="db_name_hint" class="text-slate-400 font-normal"></span></label>
      <input id="DB_DATABASE" name="DB_DATABASE" value="{{ old('DB_DATABASE', $values['DB_DATABASE'] ?? '') }}" required
        class="w-full border rounded-lg px-3 py-2 text-sm" placeholder="forge">
    </div>
    <div id="row_DB_USERNAME">
      <label class="block text-sm font-medium mb-1" for="DB_USERNAME">Username</label>
      <input id="DB_USERNAME" name="DB_USERNAME" value="{{ old('DB_USERNAME', $values['DB_USERNAME'] ?? '') }}"
        class="w-full border rounded-lg px-3 py-2 text-sm" autocomplete="off">
    </div>
    <div id="row_DB_PASSWORD">
      <label class="block text-sm font-medium mb-1" for="DB_PASSWORD">Password</label>
      <input id="DB_PASSWORD" name="DB_PASSWORD" type="password" value="{{ old('DB_PASSWORD', $values['DB_PASSWORD'] ?? '') }}"
        class="w-full border rounded-lg px-3 py-2 text-sm" autocomplete="new-password">
    </div>
  </div>

<script>
(function () {
  var conn = document.getElementById('DB_CONNECTION');
  var hint = document.getElementById('db_name_hint');
  var serverFields = ['DB_HOST', 'DB_PORT', 'DB_USERNAME', 'DB_PASSWORD'];
  function toggle() {
    var sqlite = conn.value === 'sqlite';
    serverFields.forEach(function (id) {
      document.getElementById('row_' + id).style.display = sqlite ? 'none' : '';
    });
    hint.textContent = sqlite ? '(path to .sqlite file)' : '';
    if (conn.value === 'pgsql') {
      document.getElementById('DB_PORT').placeholder = '5432';
    } else {
      document.getElementById('DB_PORT').placeholder = '3306';
    }
  }
  conn.addEventListener('change', toggle);
  toggle();
})();
</script>

  <div class="flex gap-3 pt-2">
    <a href="{{ route('installer.requirements') }}" class="px-4 py-2 rounded-lg bg-slate-200 text-sm">← Back</a>
    <button type="submit" class="px-5 py-2 rounded-lg bg-slate-900 text-white text-sm">Test connection & save →</button>
  </div>
</form>
@endsection
