<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login — MedRi ERP</title>
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

<div class="min-h-screen flex" x-data="loginPage()">

  <!-- Left Brand Panel -->
  <div class="hidden lg:flex lg:w-1/2 xl:w-3/5 relative flex-col items-center justify-center overflow-hidden"
    style="background: linear-gradient(135deg, #0D2272 0%, #1B3EB6 50%, #1a4dd4 100%)">
    <div class="absolute inset-0 opacity-5">
      <svg viewBox="0 0 800 400" class="w-full h-full">
        <polyline points="0,200 100,200 130,80 170,320 200,140 240,260 270,200 800,200" fill="none" stroke="white" stroke-width="3"/>
      </svg>
    </div>
    <div class="absolute inset-0 opacity-5" style="background-image:linear-gradient(rgba(255,255,255,.4) 1px,transparent 1px),linear-gradient(90deg,rgba(255,255,255,.4) 1px,transparent 1px);background-size:40px 40px"></div>
    <div class="absolute top-1/4 left-1/4 w-64 h-64 rounded-full blur-3xl opacity-20" style="background:#22A845"></div>
    <div class="absolute bottom-1/4 right-1/4 w-48 h-48 rounded-full blur-3xl opacity-15" style="background:#E31E24"></div>
    <div class="relative z-10 flex flex-col items-center text-center px-12">
      <div class="w-20 h-20 rounded-3xl flex items-center justify-center mb-6 shadow-2xl" style="background:rgba(255,255,255,0.1);border:1px solid rgba(255,255,255,0.2);backdrop-filter:blur(8px)">
        <svg viewBox="0 0 60 60" class="w-14 h-14">
          <polyline points="6,30 14,30 18,14 24,46 30,22 36,38 40,30 54,30" fill="none" stroke="#22A845" stroke-width="4" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
      </div>
      <div class="mb-3">
        <span class="text-5xl font-black tracking-tight text-white">Med</span><span class="text-5xl font-black tracking-tight" style="color:#E31E24">Ri</span>
      </div>
      <p class="text-blue-200 text-sm font-medium mb-12 tracking-wide">Your Trusted Partner in Medical Supplies</p>
      <div class="grid grid-cols-2 gap-4 text-left w-full max-w-sm">
        <template x-for="f in features" :key="f.title">
          <div class="rounded-2xl p-4" style="background:rgba(255,255,255,0.07);border:1px solid rgba(255,255,255,0.12)">
            <div class="text-2xl mb-1.5" x-text="f.icon"></div>
            <div class="text-white text-sm font-semibold" x-text="f.title"></div>
            <div class="text-blue-200 text-xs mt-0.5 leading-relaxed" x-text="f.desc"></div>
          </div>
        </template>
      </div>
    </div>
    <p class="absolute bottom-6 text-blue-300/50 text-xs tracking-widest uppercase font-medium">Enterprise Resource Planning System</p>
  </div>

  <!-- Right Form Panel -->
  <div class="flex-1 flex items-center justify-center p-6 bg-gray-50 dark:bg-gray-900">
    <div class="w-full max-w-md">

      <!-- Mobile logo -->
      <div class="lg:hidden text-center mb-8">
        <div class="inline-flex items-center gap-3 mb-1">
          <div class="w-10 h-10 rounded-2xl flex items-center justify-center" style="background:linear-gradient(135deg,#1B3EB6,#0D2272)">
            <svg viewBox="0 0 36 36" class="w-7 h-7"><polyline points="4,18 8,18 10,10 13,26 16,14 19,22 22,18 32,18" fill="none" stroke="#22A845" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
          </div>
          <div>
            <span class="text-3xl font-black" style="color:#1B3EB6">Med</span><span class="text-3xl font-black" style="color:#E31E24">Ri</span>
          </div>
        </div>
      </div>

      <!-- Card -->
      <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-xl p-8">
        <div class="mb-8">
          <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Welcome back</h1>
          <p class="text-gray-500 dark:text-gray-400 text-sm mt-1">Sign in to your account to continue</p>
        </div>

        <!-- Error -->
        <div x-show="error" x-cloak class="mb-5 flex items-center gap-2.5 px-4 py-3 rounded-xl bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800">
          <svg class="w-4 h-4 text-red-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
          <p class="text-sm text-red-700 dark:text-red-400" x-text="error"></p>
        </div>

        <form @submit.prevent="submit" class="space-y-5">
          <!-- Email -->
          <div>
            <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 mb-1.5 uppercase tracking-wider">Email Address</label>
            <div class="relative">
              <svg class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
              <input x-model="form.email" type="email" placeholder="you@medri.lk" required autocomplete="email"
                class="w-full pl-10 pr-4 py-2.5 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-xl text-sm text-gray-900 dark:text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition"/>
            </div>
          </div>

          <!-- Password -->
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
            <span x-text="loading ? 'Signing in...' : 'Sign in to MedRi'"></span>
          </button>
        </form>

        <div class="flex items-center justify-center mt-6 pt-5 border-t border-gray-100 dark:border-gray-700">
          <button @click="toggleDark()"
            class="flex items-center gap-2 text-xs text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 transition-colors">
            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
              <path x-show="!dark" fill-rule="evenodd" d="M9.528 1.718a.75.75 0 01.162.819A8.97 8.97 0 009 6a9 9 0 009 9 8.97 8.97 0 003.463-.69.75.75 0 01.981.98 10.503 10.503 0 01-9.694 6.46c-5.799 0-10.5-4.701-10.5-10.5 0-4.368 2.667-8.112 6.46-9.694a.75.75 0 01.818.162z" clip-rule="evenodd"/>
              <path x-show="dark" d="M12 2.25a.75.75 0 01.75.75v2.25a.75.75 0 01-1.5 0V3a.75.75 0 01.75-.75zM7.5 12a4.5 4.5 0 119 0 4.5 4.5 0 01-9 0zM18.894 6.166a.75.75 0 00-1.06-1.06l-1.591 1.59a.75.75 0 101.06 1.061l1.591-1.59zM21.75 12a.75.75 0 01-.75.75h-2.25a.75.75 0 010-1.5H21a.75.75 0 01.75.75zM17.834 18.894a.75.75 0 001.06-1.06l-1.59-1.591a.75.75 0 10-1.061 1.06l1.59 1.591zM12 18a.75.75 0 01.75.75V21a.75.75 0 01-1.5 0v-2.25A.75.75 0 0112 18zM7.758 17.303a.75.75 0 00-1.061-1.06l-1.591 1.59a.75.75 0 001.06 1.061l1.592-1.59zM6 12a.75.75 0 01-.75.75H3a.75.75 0 010-1.5h2.25A.75.75 0 016 12zM6.697 7.757a.75.75 0 001.06-1.06l-1.59-1.591a.75.75 0 00-1.061 1.06l1.59 1.591z"/>
            </svg>
            <span x-text="dark ? 'Switch to Light Mode' : 'Switch to Dark Mode'"></span>
          </button>
        </div>
      </div>
      <p class="text-center text-xs text-gray-400 dark:text-gray-600 mt-6">© 2026 MedRi Medical Supplies · All rights reserved</p>
    </div>
  </div>
</div>

<script>
const API_URL = '{{ url('/api') }}';

function loginPage() {
  return {
    form: { email: 'admin@medri.lk', password: 'password', remember: false },
    loading: false,
    error: '',
    showPw: false,
    dark: localStorage.getItem('medri_dark') === 'true',
    features: [
      { icon: '🏢', title: 'Multi-Branch', desc: 'Manage all branches from one place' },
      { icon: '📊', title: 'Smart Reports', desc: 'Real-time analytics & insights' },
      { icon: '🔐', title: 'Role-Based', desc: 'Granular access control' },
      { icon: '💊', title: 'Medical Focus', desc: 'Built for medical equipment' },
    ],
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

        // Remember me checked: token survives browser restarts for 30 days.
        // Unchecked: token only lasts for this browser session (cleared on
        // close), matching the shorter 12h expiry issued by the server.
        if (this.form.remember) {
          localStorage.setItem('medri_token', data.token);
          document.cookie = 'medri_api_token=' + encodeURIComponent(data.token) + '; path=/; SameSite=Lax; max-age=' + (60 * 60 * 24 * 30);
        } else {
          sessionStorage.setItem('medri_token', data.token);
          document.cookie = 'medri_api_token=' + encodeURIComponent(data.token) + '; path=/; SameSite=Lax';
        }
        localStorage.setItem('medri_user', JSON.stringify(data.user));
        if (data.user?.default_branch_id) localStorage.setItem('medri_branch', data.user.default_branch_id);
        window.location.href = '{{ url('/') }}';
      } catch (e) {
        this.error = e.message || 'Login failed. Please check your credentials.';
      } finally {
        this.loading = false;
      }
    },
    toggleDark() {
      this.dark = !this.dark;
      localStorage.setItem('medri_dark', this.dark);
      document.documentElement.classList.toggle('dark', this.dark);
    }
  };
}
</script>
</body>
</html>
