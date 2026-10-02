<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Administrator Access — OREKS ERP</title>
  <script>
    if (localStorage.getItem('medri_token') || sessionStorage.getItem('medri_token')) window.location.replace('{{ url('/') }}');
    if (localStorage.getItem('medri_dark') === 'true') document.documentElement.classList.add('dark');
  </script>
  <script src="https://cdn.tailwindcss.com"></script>
  <script>tailwind.config = { darkMode: 'class' }</script>
  <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.9/dist/cdn.min.js"></script>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" media="print" onload="this.media='all'">
  <style>body { font-family: 'Inter', system-ui, sans-serif; } [x-cloak]{display:none!important}</style>
</head>
<body class="dark:bg-gray-900">

<div class="min-h-screen flex items-center justify-center p-6 bg-gray-50 dark:bg-gray-900" x-data="maintenanceLoginPage()">
  <div class="w-full max-w-md">

    <div class="text-center mb-8">
      <div class="inline-flex items-center justify-center rounded-2xl bg-white/95 px-6 py-3 shadow-sm">
        <img src="{{ asset('backend/public/images/oreks-logo.png') }}" alt="OREKS" style="height:40px;width:auto;object-fit:contain" />
      </div>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-xl p-8">
      <div class="mb-8">
        <div class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold uppercase tracking-wider mb-3" style="background:#fff7ed;color:#9a3412">
          <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0z"/><path stroke-linecap="round" d="M11.25 15h1.5"/></svg>
          Maintenance Mode
        </div>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Administrator Access</h1>
        <p class="text-gray-500 dark:text-gray-400 text-sm mt-1">The system is in maintenance mode. Sign in with an authorized account to continue.</p>
      </div>

      <div x-show="error" x-cloak class="mb-5 flex items-center gap-2.5 px-4 py-3 rounded-xl bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800">
        <svg class="w-4 h-4 text-red-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
        <p class="text-sm text-red-700 dark:text-red-400" x-text="error"></p>
      </div>

      <form @submit.prevent="submit" class="space-y-5">
        <div>
          <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 mb-1.5 uppercase tracking-wider">Email Address</label>
          <div class="relative">
            <svg class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
            <input x-model="form.email" type="email" placeholder="you@oreksglobal.com" required autocomplete="email"
              class="w-full pl-10 pr-4 py-2.5 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-xl text-sm text-gray-900 dark:text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition"/>
          </div>
        </div>

        <div>
          <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 mb-1.5 uppercase tracking-wider">Password</label>
          <div class="relative">
            <svg class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
            <input x-model="form.password" :type="showPw ? 'text' : 'password'" placeholder="••••••••" required autocomplete="current-password"
              class="w-full pl-10 pr-10 py-2.5 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-xl text-sm text-gray-900 dark:text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition"/>
            <button type="button" @click="showPw = !showPw" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
              <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path x-show="!showPw" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0zM2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                <path x-show="showPw" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
              </svg>
            </button>
          </div>
        </div>

        <label class="flex items-center gap-2 cursor-pointer select-none -mt-1">
          <input type="checkbox" x-model="form.remember"
            class="w-4 h-4 rounded border-gray-300 dark:border-gray-600 text-blue-600 focus:ring-blue-500 focus:ring-offset-0"/>
          <span class="text-sm text-gray-600 dark:text-gray-300">Remember me for 30 days</span>
        </label>

        <button type="submit" :disabled="loading"
          class="w-full py-3 px-4 bg-blue-600 hover:bg-blue-700 disabled:opacity-60 text-white font-semibold rounded-xl text-base transition-all focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 flex items-center justify-center gap-2">
          <svg x-show="loading" class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
          <span x-text="loading ? 'Signing in...' : 'Sign in'"></span>
        </button>
      </form>
    </div>
    <p class="text-center text-xs text-gray-400 dark:text-gray-600 mt-6">© 2026 OREKS · All rights reserved</p>
  </div>
</div>

<script>
const API_URL = '{{ url('/api') }}';

function maintenanceLoginPage() {
  return {
    form: { email: '', password: '', remember: false },
    loading: false,
    error: '',
    showPw: false,
    async submit() {
      this.error = '';
      this.loading = true;
      try {
        const res = await fetch(API_URL + '/auth/login', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
          body: JSON.stringify(this.form),
        });
        const data = await res.json();
        if (!res.ok) throw new Error(data.message || 'Login failed');

        if (this.form.remember) {
          localStorage.setItem('medri_token', data.token);
          document.cookie = 'medri_api_token=' + encodeURIComponent(data.token) + '; path=/; SameSite=Lax; max-age=' + (60 * 60 * 24 * 30);
        } else {
          sessionStorage.setItem('medri_token', data.token);
          document.cookie = 'medri_api_token=' + encodeURIComponent(data.token) + '; path=/; SameSite=Lax';
        }
        localStorage.setItem('medri_user', JSON.stringify(data.user));
        if (data.user?.default_branch_id) localStorage.setItem('medri_branch', data.user.default_branch_id);
        // Regular (non-bypass-eligible) accounts can still authenticate here —
        // they just land straight back on the maintenance page, since the
        // server-side check is the real gate, not knowledge of this URL.
        window.location.href = '{{ url('/') }}';
      } catch (e) {
        this.error = e.message || 'Login failed. Please check your credentials.';
      } finally {
        this.loading = false;
      }
    },
  };
}
</script>
</body>
</html>
