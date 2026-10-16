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

<form id="env-form" method="POST" action="{{ route('installer.environment.save') }}" class="space-y-4">
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

  <div id="live-result" class="hidden text-sm p-3 rounded-lg"></div>

  <div class="flex flex-col sm:flex-row gap-3 pt-2">
    <a href="{{ route('installer.requirements') }}" class="px-4 py-2 rounded-lg bg-slate-200 text-sm text-center">← Back</a>
    <button type="button" id="test-btn" class="px-4 py-2 rounded-lg bg-slate-600 text-white text-sm">Test connection</button>
    <button type="submit" id="save-btn" class="px-5 py-2 rounded-lg bg-slate-900 text-white text-sm">Save & continue →</button>
  </div>
</form>

<div id="toast-box" class="fixed top-4 right-4 space-y-2 z-50 max-w-xs w-[calc(100%-2rem)]"></div>

<script>
(function () {
  var form = document.getElementById('env-form');
  var testBtn = document.getElementById('test-btn');
  var saveBtn = document.getElementById('save-btn');
  var result = document.getElementById('live-result');
  var box = document.getElementById('toast-box');
  var token = form.querySelector('input[name="_token"]').value;
  var saveUrl = form.action;
  var testUrl = @json(route('installer.api.test-connection'));
  var statusUrl = @json(route('installer.api.status'));

  function toast(message, kind) {
    var colors = { ok: 'bg-green-600', err: 'bg-red-600', wait: 'bg-amber-500' };
    var el = document.createElement('div');
    el.className = 'text-white text-sm px-4 py-2 rounded-lg shadow ' + (colors[kind] || colors.ok);
    el.textContent = message;
    box.appendChild(el);
    setTimeout(function () { el.remove(); }, kind === 'wait' ? 12000 : 6000);
    return el;
  }

  function showResult(ok, message) {
    result.classList.remove('hidden', 'bg-green-50', 'text-green-800', 'bg-red-50', 'text-red-800');
    result.classList.add(ok ? 'bg-green-50' : 'bg-red-50', ok ? 'text-green-800' : 'text-red-800');
    result.textContent = message;
  }

  function payload() {
    var data = {};
    new FormData(form).forEach(function (v, k) { data[k] = v; });
    return data;
  }

  function postJson(url, body) {
    return fetch(url, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': token, 'X-Requested-With': 'XMLHttpRequest' },
      body: JSON.stringify(body)
    }).then(function (res) {
      return res.json().then(function (json) { return { http: res.status, json: json }; });
    });
  }

  function setBusy(btn, busy, label) {
    btn.disabled = busy;
    btn.dataset.label = btn.dataset.label || btn.textContent;
    btn.textContent = busy ? (label || 'Working…') : btn.dataset.label;
    btn.classList.toggle('opacity-60', busy);
  }

  // Live probe: no .env write, no restart, no reload.
  testBtn.addEventListener('click', function () {
    setBusy(testBtn, true, 'Testing…');
    postJson(testUrl, payload()).then(function (out) {
      setBusy(testBtn, false);
      if (out.http === 200 && out.json.ok) {
        showResult(true, '✓ ' + out.json.message);
        toast('Connection works.', 'ok');
      } else if (out.json.errors) {
        showResult(false, '✗ ' + Object.values(out.json.errors).flat().join(' '));
        toast('Fix the highlighted fields.', 'err');
      } else {
        showResult(false, '✗ ' + (out.json.message || 'Connection failed.'));
        toast(out.json.message || 'Connection failed.', 'err');
      }
    }).catch(function () {
      setBusy(testBtn, false);
      showResult(false, '✗ Could not reach the server.');
      toast('Could not reach the server.', 'err');
    });
  });

  // Save via fetch. If the dev server restarts on .env write and drops the
  // connection, poll /api/status until it is back, then continue.
  form.addEventListener('submit', function (e) {
    e.preventDefault();
    setBusy(saveBtn, true, 'Saving…');
    postJson(saveUrl, payload()).then(function (out) {
      if (out.http === 200 && out.json.ok) {
        toast(out.json.message, 'ok');
        window.location.href = out.json.redirect;
      } else if (out.json.errors) {
        setBusy(saveBtn, false);
        showResult(false, '✗ ' + Object.values(out.json.errors).flat().join(' '));
        toast('Fix the highlighted fields.', 'err');
      } else {
        setBusy(saveBtn, false);
        showResult(false, '✗ ' + (out.json.message || 'Save failed.'));
        toast(out.json.message || 'Save failed.', 'err');
      }
    }).catch(function () {
      waitForServer(0);
    });
  });

  function waitForServer(tries) {
    var note = toast('Saving… server restarting, waiting…', 'wait');
    (function poll(n) {
      fetch(statusUrl, { headers: { 'Accept': 'application/json' } }).then(function (res) {
        if (!res.ok) throw 0;
        return res.json();
      }).then(function (st) {
        note.remove();
        toast('Environment saved.', 'ok');
        window.location.href = st.next || @json(route('installer.database'));
      }).catch(function () {
        if (n >= 20) {
          note.remove();
          setBusy(saveBtn, false);
          showResult(false, '✗ Server did not come back. Refresh the page — your data was saved.');
          toast('Refresh the page to continue.', 'err');
          return;
        }
        setTimeout(function () { poll(n + 1); }, 1500);
      });
    })(0);
  }
})();
</script>
@endsection
