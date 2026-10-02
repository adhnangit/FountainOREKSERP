<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta http-equiv="refresh" content="60">
  <title>Maintenance — OREKS ERP</title>
  <script>
    if (localStorage.getItem('medri_dark') === 'true') document.documentElement.classList.add('dark');
  </script>
  <script src="https://cdn.tailwindcss.com"></script>
  <script>tailwind.config = { darkMode: 'class' }</script>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" media="print" onload="this.media='all'">
  <style>body { font-family: 'Inter', system-ui, sans-serif; }</style>
</head>
<body class="dark:bg-gray-900">

<div class="min-h-screen flex items-center justify-center p-6 bg-gray-50 dark:bg-gray-900">
  <div class="w-full max-w-md text-center">
    <div class="inline-flex items-center justify-center mb-8 rounded-3xl bg-white/95 px-6 py-4 shadow-sm">
      <img src="{{ asset('backend/public/images/oreks-logo.png') }}" alt="OREKS" style="height:48px;width:auto;object-fit:contain" />
    </div>

    <div class="w-16 h-16 rounded-2xl flex items-center justify-center mx-auto mb-6" style="background:#fff7ed">
      <svg class="w-8 h-8" style="color:#9a3412" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
        <path stroke-linecap="round" stroke-linejoin="round" d="M11.42 15.17L17.25 21A2.652 2.652 0 0021 17.25l-5.877-5.877M11.42 15.17l2.496-3.03c.317-.384.74-.626 1.208-.766M11.42 15.17l-4.655 5.653a2.548 2.548 0 11-3.586-3.586l6.837-5.63m5.108-.233c.55-.164 1.163-.188 1.743-.14a4.5 4.5 0 004.486-6.336l-3.276 3.277a3.004 3.004 0 01-2.25-2.25l3.276-3.276a4.5 4.5 0 00-6.336 4.486c.091 1.076-.071 2.264-.904 2.95l-.102.085m-1.745 1.437L5.909 7.5H4.5L1.5 3l1.5-1.5L7.5 4.5v1.409l4.26 4.26m-1.745 1.437l1.745-1.437m6.615 8.206L15.75 15.75M4.867 19.125h.008v.008h-.008v-.008z"/>
      </svg>
    </div>

    <h1 class="text-xl font-bold text-gray-900 dark:text-white mb-2">Under Maintenance</h1>
    <p class="text-sm text-gray-500 dark:text-gray-400 leading-relaxed">{{ $message }}</p>

    <p class="text-xs text-gray-400 dark:text-gray-600 mt-10">This page will refresh automatically.</p>
  </div>
</div>

</body>
</html>
