<?php $__env->startSection('title', 'Dashboard Widget Settings'); ?>
<?php $__env->startSection('page-title', 'Dashboard Widgets'); ?>
<?php $__env->startSection('page-desc', 'Configure which widgets each role can see on the dashboard'); ?>

<?php $__env->startSection('content'); ?>
<div x-data="widgetSettings()" x-init="init()" class="pb-12">

  
  <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
    <div>
      <p class="text-sm text-gray-500 mt-0.5">Choose which dashboard sections are visible for each user role.</p>
    </div>
    <div class="flex items-center gap-2">
      <button @click="saveRole()" :disabled="saving"
              class="btn-primary flex items-center gap-2">
        <svg x-show="saving" class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
          <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/>
        </svg>
        <svg x-show="!saving" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M5 13l4 4L19 7"/></svg>
        <span x-text="saving ? 'Saving…' : 'Save Changes'"></span>
      </button>
    </div>
  </div>

  <div x-show="loading" class="flex items-center justify-center py-24">
    <svg class="animate-spin w-8 h-8 text-indigo-500" fill="none" viewBox="0 0 24 24">
      <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
      <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/>
    </svg>
  </div>

  <div x-show="!loading" class="grid grid-cols-1 lg:grid-cols-4 gap-5">

    
    <div class="space-y-2">
      <p class="text-xs font-bold text-gray-400 uppercase tracking-wider px-1 mb-3">Roles</p>
      <template x-for="r in roles" :key="r.key">
        <button @click="selectRole(r.key)"
                class="w-full flex items-center gap-3 px-3.5 py-3 rounded-xl text-left transition-all"
                :class="activeRole === r.key
                  ? 'text-white shadow-md'
                  : 'bg-white dark:bg-gray-800 text-gray-600 dark:text-gray-300 border border-gray-100 dark:border-gray-700 hover:border-indigo-200'"
                :style="activeRole === r.key ? `background:${r.color}` : ''">
          <span class="text-lg" x-text="r.icon"></span>
          <div class="flex-1 min-w-0">
            <div class="text-sm font-semibold capitalize" x-text="r.label"></div>
            <div class="text-xs opacity-70 mt-0.5"
                 x-text="visibleCount(r.key) + ' widgets visible'"></div>
          </div>
          <div class="w-2 h-2 rounded-full flex-shrink-0"
               :class="visibleCount(r.key) > 0 ? 'bg-green-400' : 'bg-gray-300'"></div>
        </button>
      </template>
    </div>

    
    <div class="lg:col-span-3">
      <div class="card overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between"
             :style="'background:' + (roles.find(r=>r.key===activeRole)?.color ?? '#1B3EB6')">
          <div class="flex items-center gap-3">
            <span class="text-2xl" x-text="roles.find(r=>r.key===activeRole)?.icon ?? '👤'"></span>
            <div>
              <h3 class="text-sm font-bold text-white capitalize"
                  x-text="roles.find(r=>r.key===activeRole)?.label ?? activeRole"></h3>
              <p class="text-xs mt-0.5" style="color:rgba(255,255,255,0.65)"
                 x-text="visibleCount(activeRole) + ' of ' + (settings[activeRole]?.length ?? 0) + ' widgets visible'"></p>
            </div>
          </div>
          <div class="flex items-center gap-2">
            <button @click="toggleAll(true)"
                    class="text-xs font-semibold px-3 py-1.5 rounded-lg transition-all"
                    style="background:rgba(255,255,255,0.18);color:#fff;border:1px solid rgba(255,255,255,0.25)">
              Show All
            </button>
            <button @click="toggleAll(false)"
                    class="text-xs font-semibold px-3 py-1.5 rounded-lg transition-all"
                    style="background:rgba(0,0,0,0.2);color:rgba(255,255,255,0.8);border:1px solid rgba(255,255,255,0.15)">
              Hide All
            </button>
          </div>
        </div>

        <div class="divide-y divide-gray-50 dark:divide-gray-700/40">
          <template x-for="(widget, idx) in (settings[activeRole] ?? [])" :key="widget.key">
            <div class="flex items-center gap-4 px-5 py-3.5 hover:bg-gray-50/50 dark:hover:bg-gray-800/20 transition-colors">

              
              <svg class="w-4 h-4 text-gray-300 cursor-grab flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path d="M4 8h16M4 12h16M4 16h16"/>
              </svg>

              
              <div class="w-9 h-9 rounded-xl flex items-center justify-center flex-shrink-0"
                   :style="widget.is_visible ? 'background:#e0e7ff' : 'background:#f3f4f6'">
                <span class="text-base" x-text="widgetIcon(widget.key)"></span>
              </div>

              
              <div class="flex-1 min-w-0">
                <div class="text-sm font-semibold text-gray-800 dark:text-gray-100"
                     :class="!widget.is_visible ? 'opacity-40' : ''"
                     x-text="widgetTitle(widget.key)"></div>
                <div class="text-xs text-gray-400 mt-0.5" x-text="widget.label"></div>
              </div>

              
              <button @click="widget.is_visible = !widget.is_visible"
                      class="relative w-11 h-6 rounded-full transition-all flex-shrink-0 focus:outline-none"
                      :style="widget.is_visible ? 'background:#4f46e5' : 'background:#e5e7eb'">
                <span class="absolute top-0.5 left-0.5 w-5 h-5 rounded-full bg-white shadow transition-transform"
                      :style="widget.is_visible ? 'transform:translateX(20px)' : ''"></span>
              </button>
            </div>
          </template>
        </div>

        <div class="px-5 py-3 border-t border-gray-100 dark:border-gray-700 flex items-center justify-between text-xs text-gray-400"
             style="background:#fafafa">
          <span>Changes apply immediately for all users with this role</span>
          <button @click="saveRole()" :disabled="saving"
                  class="text-indigo-600 font-semibold hover:text-indigo-800 disabled:opacity-50"
                  x-text="saving ? 'Saving…' : 'Save'"></button>
        </div>
      </div>

      
      <div class="mt-4 p-4 rounded-xl flex items-start gap-3 text-sm"
           style="background:#eff6ff;border:1px solid #bfdbfe">
        <svg class="w-5 h-5 text-blue-500 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
          <path d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        <div>
          <div class="font-semibold text-blue-800">Role-based Dashboard</div>
          <div class="text-blue-600 mt-0.5">
            Users with the <strong x-text="activeRole?.replace('_',' ')"></strong> role will only see the widgets you've enabled above.
            Changes take effect on the user's next page load.
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
function widgetSettings() {
  return {
    loading: true,
    saving: false,
    activeRole: 'sales_person',
    settings: {},

    roles: [
      { key: 'super_admin',       label: 'Super Admin',       icon: '👑', color: '#1B3EB6' },
      { key: 'branch_manager',    label: 'Branch Manager',    icon: '🏢', color: '#065f46' },
      { key: 'sales_person',      label: 'Sales Person',      icon: '💼', color: '#d97706' },
      { key: 'accountant',        label: 'Accountant',        icon: '📊', color: '#7c3aed' },
      { key: 'inventory_manager', label: 'Inventory Manager', icon: '📦', color: '#0891b2' },
      { key: 'purchase_officer',  label: 'Purchase Officer',  icon: '🛒', color: '#059669' },
      { key: 'hr_admin',          label: 'HR Admin',          icon: '👥', color: '#dc2626' },
      { key: 'viewer',            label: 'Viewer',            icon: '👁', color: '#6b7280' },
    ],

    async init() {
      try {
        const r = await apiFetch('/dashboard-widget-settings/admin');
        this.settings = await r.json();
      } catch(e) {
        toast('Failed to load widget settings', 'error');
      } finally {
        this.loading = false;
      }
    },

    selectRole(key) { this.activeRole = key; },

    visibleCount(role) {
      return (this.settings[role] ?? []).filter(w => w.is_visible).length;
    },

    toggleAll(visible) {
      (this.settings[this.activeRole] ?? []).forEach(w => { w.is_visible = visible; });
    },

    async saveRole() {
      this.saving = true;
      try {
        const r = await apiFetch('/dashboard-widget-settings', {
          method: 'POST',
          body: JSON.stringify({
            role: this.activeRole,
            widgets: (this.settings[this.activeRole] ?? []).map((w, i) => ({
              key: w.key, is_visible: w.is_visible, sort_order: i,
            })),
          }),
        });
        if (r.ok) {
          toast('Widget settings saved for ' + this.activeRole.replace('_', ' '), 'success');
        } else {
          const e = await r.json();
          toast(e.message ?? 'Save failed', 'error');
        }
      } finally { this.saving = false; }
    },

    widgetIcon(key) {
      const m = {
        kpi_overview:      '📈',
        financials:        '💰',
        revenue_chart:     '📉',
        cheque_summary:    '🏦',
        branch_performance:'🏢',
        target_progress:   '🎯',
        today_sales:       '🧾',
        sales_reps_aging:  '👤',
        charts:            '🥧',
        due_tables:        '📅',
        low_stock:         '⚠️',
      };
      return m[key] ?? '📌';
    },

    widgetTitle(key) {
      const m = {
        kpi_overview:      'KPI Overview',
        financials:        'Financial KPIs',
        revenue_chart:     'Revenue Chart',
        cheque_summary:    'Cheque Summary',
        branch_performance:'Branch Performance',
        target_progress:   'Target Progress',
        today_sales:       'Sales Report',
        sales_reps_aging:  'Sales Reps & Aging',
        charts:            'Product & Expense Charts',
        due_tables:        'Monthly Due Tables',
        low_stock:         'Low Stock Alerts',
      };
      return m[key] ?? key;
    },
  };
}
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/medrilk/system.medri.lk/backend/resources/views/settings/dashboard-widgets.blade.php ENDPATH**/ ?>