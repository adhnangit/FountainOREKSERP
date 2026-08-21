<?php $__env->startSection('title', 'Sales Targets'); ?>
<?php $__env->startSection('page-title', 'Sales Targets'); ?>
<?php $__env->startSection('page-desc', 'Set and monitor branch & sales rep targets'); ?>

<?php $__env->startSection('content'); ?>
<style>
.tgt-tab{padding:6px 18px;border-radius:8px;font-size:13px;font-weight:600;cursor:pointer;border:none;transition:all .15s;font-family:inherit}
.tgt-tab.active{background:#1B3EB6;color:#fff;box-shadow:0 2px 8px rgba(27,62,182,.35)}
.tgt-tab:not(.active){background:#f1f5f9;color:#64748b}
.tgt-tab:not(.active):hover{background:#e2e8f0;color:#334155}
.tgt-card{background:#fff;border-radius:12px;border:1px solid #e2e8f0;padding:16px 18px;transition:box-shadow .15s}
.tgt-card:hover{box-shadow:0 4px 16px rgba(0,0,0,.08)}
.dark .tgt-card{background:#1e293b;border-color:#334155}
.tgt-pbar{height:8px;background:#e2e8f0;border-radius:6px;overflow:hidden;margin:6px 0}
.dark .tgt-pbar{background:#334155}
.tgt-pbar-fill{height:100%;border-radius:6px;transition:width .6s ease}
.tgt-badge{display:inline-flex;align-items:center;padding:2px 10px;border-radius:20px;font-size:11px;font-weight:700}
.tgt-badge-branch{background:#dbeafe;color:#1d4ed8}
.tgt-badge-rep{background:#d1fae5;color:#065f46}
.tgt-badge-ann{background:#fef3c7;color:#92400e}
.tgt-sect-title{font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.08em;color:#94a3b8;margin:18px 0 10px;display:flex;align-items:center;gap:8px}
.tgt-sect-title::after{content:'';flex:1;height:1px;background:#e2e8f0}
.dark .tgt-sect-title::after{background:#334155}
</style>

<div x-data="targetsPage()" x-init="init()">

  
  <div class="flex flex-wrap items-center justify-between gap-3 mb-5">

    
    <div class="flex gap-2">
      <button class="tgt-tab" :class="tab==='monthly'?'active':''" @click="tab='monthly';load()">Monthly</button>
      <button class="tgt-tab" :class="tab==='annual'?'active':''"  @click="tab='annual';load()">Annual</button>
    </div>

    
    <div class="flex items-center gap-2 flex-wrap">
      <select x-model.number="selYear" @change="load();loadAllocation()" class="input w-28 text-sm">
        <template x-for="y in years" :key="y">
          <option :value="y" x-text="y"></option>
        </template>
      </select>
      <template x-if="tab==='monthly'">
        <select x-model.number="selMonth" @change="load()" class="input w-36 text-sm">
          <template x-for="m in months" :key="m.v">
            <option :value="m.v" x-text="m.l"></option>
          </template>
        </select>
      </template>
      <template x-if="tab==='annual'">
        <span class="text-sm text-gray-500 font-medium">Full Year <span x-text="selYear"></span></span>
      </template>

      
      <template x-if="isAdmin">
        <button @click="openCreate()"
                style="background:#1B3EB6"
                class="flex items-center gap-1.5 px-4 py-2 rounded-xl text-sm font-semibold text-white shadow transition-opacity hover:opacity-90 ml-2">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="3"><path d="M12 5v14M5 12h14"/></svg>
          Set Target
        </button>
      </template>
    </div>
  </div>

  
  <div x-show="loading" class="flex items-center justify-center py-20">
    <svg class="animate-spin w-8 h-8 text-blue-500" fill="none" viewBox="0 0 24 24">
      <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
      <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/>
    </svg>
  </div>

  <div x-show="!loading">

    
    <div class="grid grid-cols-3 gap-3 mb-4">
      <div class="tgt-card text-center">
        <div class="text-[10px] text-gray-400 uppercase tracking-wider mb-1">Total Targets</div>
        <div class="text-2xl font-black text-gray-800 dark:text-gray-100" x-text="items.length"></div>
      </div>
      <div class="tgt-card text-center">
        <div class="text-[10px] text-green-500 uppercase tracking-wider mb-1">Achieved ≥100%</div>
        <div class="text-2xl font-black text-green-600" x-text="items.filter(t=>t.pct>=100).length"></div>
      </div>
      <div class="tgt-card text-center">
        <div class="text-[10px] text-red-500 uppercase tracking-wider mb-1">Below 70%</div>
        <div class="text-2xl font-black text-red-600" x-text="items.filter(t=>t.pct<70).length"></div>
      </div>
    </div>

    
    <div class="tgt-sect-title">
      <svg style="width:13px;height:13px;flex-shrink:0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
        <path d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
      </svg>
      Branch Targets
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-3">
      <template x-for="t in branchTargets" :key="t.id">
        <div class="tgt-card">
          <div class="flex items-start justify-between mb-2">
            <div>
              <div class="font-semibold text-gray-800 dark:text-gray-100 text-sm" x-text="t.branch_name"></div>
              <div class="flex items-center gap-1.5 mt-1 flex-wrap">
                <span class="tgt-badge tgt-badge-branch" x-text="tab==='annual'?'Annual':'Monthly'"></span>
                <span class="tgt-badge" style="background:#f1f5f9;color:#475569;font-size:10px" x-text="typeLabel(t.type)"></span>
              </div>
            </div>
            <div class="flex items-center gap-1.5 flex-shrink-0">
              <span class="tgt-badge text-xs"
                    :style="t.pct>=100?'background:#dcfce7;color:#166534':t.pct>=70?'background:#fef9c3;color:#854d0e':'background:#fee2e2;color:#b91c1c'"
                    x-text="t.pct+'%'"></span>
              <template x-if="isAdmin">
                <div class="flex gap-1">
                  <button @click="openEdit(t)" title="Edit"
                          style="width:24px;height:24px;border-radius:6px;border:1px solid #e2e8f0;background:#f8fafc;display:flex;align-items:center;justify-content:center;cursor:pointer;transition:background .15s"
                          onmouseover="this.style.background='#e0e7ff'" onmouseout="this.style.background='#f8fafc'">
                    <svg style="width:12px;height:12px;color:#475569" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                      <path d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                    </svg>
                  </button>
                  <button @click="confirmDelete(t)" title="Delete"
                          style="width:24px;height:24px;border-radius:6px;border:1px solid #fee2e2;background:#fff;display:flex;align-items:center;justify-content:center;cursor:pointer;transition:background .15s"
                          onmouseover="this.style.background='#fee2e2'" onmouseout="this.style.background='#fff'">
                    <svg style="width:12px;height:12px;color:#ef4444" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                      <path d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                    </svg>
                  </button>
                </div>
              </template>
            </div>
          </div>
          <div class="tgt-pbar">
            <div class="tgt-pbar-fill"
                 :style="'width:'+Math.min(t.pct,100)+'%;background:'+(t.pct>=100?'#22c55e':t.pct>=70?'#f59e0b':'#ef4444')"></div>
          </div>
          <div class="grid grid-cols-2 gap-2 mt-2 text-xs">
            <div>
              <span class="block text-gray-400">Target</span>
              <span class="font-semibold text-gray-700 dark:text-gray-300" x-text="fmtMoney(t.target_value)"></span>
            </div>
            <div>
              <span class="block text-gray-400">Achieved</span>
              <span class="font-semibold" :class="t.pct>=100?'text-green-600':'text-gray-700 dark:text-gray-300'" x-text="fmtMoney(t.achieved_value)"></span>
            </div>
          </div>
          <div x-show="t.notes" class="mt-2 text-[11px] text-gray-400 italic" x-text="t.notes"></div>
          
          <template x-if="tab==='monthly' && allocInfo(t)">
            <div class="mt-2 pt-2 border-t border-gray-100 dark:border-gray-700/40">
              <div class="flex items-center justify-between text-[10px] mb-1">
                <span class="text-gray-400 font-medium">vs Annual Target</span>
                <span class="font-bold"
                      :style="allocInfo(t).allocated>allocInfo(t).annual?'color:#dc2626':allocInfo(t).allocated>=allocInfo(t).annual?'color:#16a34a':'color:#d97706'"
                      x-text="allocInfo(t).annual>0?Math.round((allocInfo(t).allocated/allocInfo(t).annual)*100)+'% allocated':'—'"></span>
              </div>
              <div class="tgt-pbar" style="height:4px">
                <div class="tgt-pbar-fill"
                     :style="'width:'+Math.min(allocInfo(t).annual>0?Math.round((allocInfo(t).allocated/allocInfo(t).annual)*100):0,100)+'%;background:'+(allocInfo(t).allocated>allocInfo(t).annual?'#ef4444':allocInfo(t).allocated>=allocInfo(t).annual?'#22c55e':'#f59e0b')"></div>
              </div>
              <div class="flex justify-between text-[10px] mt-1">
                <span class="text-gray-400" x-text="'Annual: '+fmtTv(allocInfo(t).annual,t.type)"></span>
                <span :style="allocInfo(t).allocated>allocInfo(t).annual?'color:#dc2626;font-weight:600':allocInfo(t).allocated>=allocInfo(t).annual?'color:#16a34a':'color:#d97706'"
                      x-text="allocInfo(t).allocated>allocInfo(t).annual?'⚠ Over '+fmtTv(allocInfo(t).allocated-allocInfo(t).annual,t.type):allocInfo(t).allocated>=allocInfo(t).annual?'✓ Fully allocated':fmtTv(allocInfo(t).annual-allocInfo(t).allocated,t.type)+' unset'"></span>
              </div>
            </div>
          </template>
        </div>
      </template>
      <template x-if="branchTargets.length===0">
        <div class="sm:col-span-2 xl:col-span-3 tgt-card text-center py-8 text-gray-400 text-sm">
          No branch targets set for this period.
          <template x-if="isAdmin">
            <span> <button @click="openCreate('branch')" class="text-blue-600 hover:underline font-medium">Set one now</button></span>
          </template>
        </div>
      </template>
    </div>

    
    <div class="tgt-sect-title mt-2">
      <svg style="width:13px;height:13px;flex-shrink:0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
        <path d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
      </svg>
      Sales Rep Targets
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-3">
      <template x-for="t in repTargets" :key="t.id">
        <div class="tgt-card">
          <div class="flex items-start justify-between mb-2">
            <div>
              <div class="font-semibold text-gray-800 dark:text-gray-100 text-sm" x-text="t.user_name"></div>
              <div class="flex items-center gap-1.5 mt-1 flex-wrap">
                <span class="tgt-badge tgt-badge-rep" x-text="t.branch_name"></span>
                <span class="tgt-badge" style="background:#f1f5f9;color:#475569;font-size:10px" x-text="typeLabel(t.type)"></span>
              </div>
            </div>
            <div class="flex items-center gap-1.5 flex-shrink-0">
              <span class="tgt-badge text-xs"
                    :style="t.pct>=100?'background:#dcfce7;color:#166534':t.pct>=70?'background:#fef9c3;color:#854d0e':'background:#fee2e2;color:#b91c1c'"
                    x-text="t.pct+'%'"></span>
              <template x-if="isAdmin">
                <div class="flex gap-1">
                  <button @click="openEdit(t)"
                          style="width:24px;height:24px;border-radius:6px;border:1px solid #e2e8f0;background:#f8fafc;display:flex;align-items:center;justify-content:center;cursor:pointer;transition:background .15s"
                          onmouseover="this.style.background='#e0e7ff'" onmouseout="this.style.background='#f8fafc'">
                    <svg style="width:12px;height:12px;color:#475569" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                      <path d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                    </svg>
                  </button>
                  <button @click="confirmDelete(t)"
                          style="width:24px;height:24px;border-radius:6px;border:1px solid #fee2e2;background:#fff;display:flex;align-items:center;justify-content:center;cursor:pointer;transition:background .15s"
                          onmouseover="this.style.background='#fee2e2'" onmouseout="this.style.background='#fff'">
                    <svg style="width:12px;height:12px;color:#ef4444" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                      <path d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                    </svg>
                  </button>
                </div>
              </template>
            </div>
          </div>
          <div class="tgt-pbar">
            <div class="tgt-pbar-fill"
                 :style="'width:'+Math.min(t.pct,100)+'%;background:'+(t.pct>=100?'#22c55e':t.pct>=70?'#f59e0b':'#ef4444')"></div>
          </div>
          <div class="grid grid-cols-2 gap-2 mt-2 text-xs">
            <div>
              <span class="block text-gray-400">Target</span>
              <span class="font-semibold text-gray-700 dark:text-gray-300" x-text="fmtMoney(t.target_value)"></span>
            </div>
            <div>
              <span class="block text-gray-400">Achieved</span>
              <span class="font-semibold" :class="t.pct>=100?'text-green-600':'text-gray-700 dark:text-gray-300'" x-text="fmtMoney(t.achieved_value)"></span>
            </div>
          </div>
          <div x-show="t.notes" class="mt-2 text-[11px] text-gray-400 italic" x-text="t.notes"></div>
          
          <template x-if="tab==='monthly' && allocInfo(t)">
            <div class="mt-2 pt-2 border-t border-gray-100 dark:border-gray-700/40">
              <div class="flex items-center justify-between text-[10px] mb-1">
                <span class="text-gray-400 font-medium">vs Annual Target</span>
                <span class="font-bold"
                      :style="allocInfo(t).allocated>allocInfo(t).annual?'color:#dc2626':allocInfo(t).allocated>=allocInfo(t).annual?'color:#16a34a':'color:#d97706'"
                      x-text="allocInfo(t).annual>0?Math.round((allocInfo(t).allocated/allocInfo(t).annual)*100)+'% allocated':'—'"></span>
              </div>
              <div class="tgt-pbar" style="height:4px">
                <div class="tgt-pbar-fill"
                     :style="'width:'+Math.min(allocInfo(t).annual>0?Math.round((allocInfo(t).allocated/allocInfo(t).annual)*100):0,100)+'%;background:'+(allocInfo(t).allocated>allocInfo(t).annual?'#ef4444':allocInfo(t).allocated>=allocInfo(t).annual?'#22c55e':'#f59e0b')"></div>
              </div>
              <div class="flex justify-between text-[10px] mt-1">
                <span class="text-gray-400" x-text="'Annual: '+fmtTv(allocInfo(t).annual,t.type)"></span>
                <span :style="allocInfo(t).allocated>allocInfo(t).annual?'color:#dc2626;font-weight:600':allocInfo(t).allocated>=allocInfo(t).annual?'color:#16a34a':'color:#d97706'"
                      x-text="allocInfo(t).allocated>allocInfo(t).annual?'⚠ Over '+fmtTv(allocInfo(t).allocated-allocInfo(t).annual,t.type):allocInfo(t).allocated>=allocInfo(t).annual?'✓ Fully allocated':fmtTv(allocInfo(t).annual-allocInfo(t).allocated,t.type)+' unset'"></span>
              </div>
            </div>
          </template>
        </div>
      </template>
      <template x-if="repTargets.length===0">
        <div class="sm:col-span-2 xl:col-span-3 tgt-card text-center py-8 text-gray-400 text-sm">
          No sales rep targets set for this period.
          <template x-if="isAdmin">
            <span> <button @click="openCreate('rep')" class="text-blue-600 hover:underline font-medium">Set one now</button></span>
          </template>
        </div>
      </template>
    </div>

  </div>

  
  <div x-show="showModal" class="fixed inset-0 z-50 flex items-center justify-center p-4"
       x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0"
       x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150"
       x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
    <div class="absolute inset-0" style="background:rgba(15,23,42,.65);backdrop-filter:blur(4px)" @click="showModal=false"></div>
    <div class="relative bg-white dark:bg-gray-900 rounded-2xl shadow-2xl w-full max-w-lg p-6 z-10 overflow-y-auto" style="max-height:90vh">

      <div class="flex items-start justify-between mb-5">
        <div>
          <h5 class="text-lg font-bold text-gray-800 dark:text-gray-100"
              x-text="editTarget?'Edit Target':'Set New Target'"></h5>
          <p class="text-sm text-gray-400 mt-0.5" x-text="tab==='annual'?'Annual target for '+selYear:'Monthly target — '+monthName(selMonth)+' '+selYear"></p>
        </div>
        <button @click="showModal=false" class="text-gray-400 hover:text-gray-600 ml-4">
          <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M6 18L18 6M6 6l12 12"/></svg>
        </button>
      </div>

      <div class="space-y-4">

        <template x-if="!editTarget">
          
          <div>
            <label class="label text-xs">Target For <span class="text-red-500">*</span></label>
            <div class="flex gap-2">
              <button @click="form.target_for='branch'"
                      class="flex-1 py-2 rounded-lg text-sm font-semibold border transition-all"
                      :class="form.target_for==='branch'?'bg-blue-600 text-white border-blue-600':'bg-gray-50 text-gray-600 border-gray-200 hover:border-blue-300'">
                Branch
              </button>
              <button @click="form.target_for='rep'"
                      class="flex-1 py-2 rounded-lg text-sm font-semibold border transition-all"
                      :class="form.target_for==='rep'?'bg-green-600 text-white border-green-600':'bg-gray-50 text-gray-600 border-gray-200 hover:border-green-300'">
                Sales Rep
              </button>
            </div>
          </div>
        </template>

        
        <div>
          <label class="label text-xs">Branch <span class="text-red-500">*</span></label>
          <select x-model.number="form.branch_id" class="input" :disabled="editTarget">
            <option value="">— Select Branch —</option>
            <template x-for="b in branches" :key="b.id">
              <option :value="b.id" x-text="b.name"></option>
            </template>
          </select>
        </div>

        
        <template x-if="form.target_for==='rep'||editTarget?.user_id">
          <div>
            <label class="label text-xs">Sales Rep <span class="text-red-500">*</span></label>
            <select x-model.number="form.user_id" class="input">
              <option value="">— Select Sales Rep —</option>
              <template x-for="u in users" :key="u.id">
                <option :value="u.id" x-text="u.name+' ('+u.branch+')'"></option>
              </template>
            </select>
          </div>
        </template>

        
        <template x-if="!editTarget">
          <div>
            <label class="label text-xs">Target Type <span class="text-red-500">*</span></label>
            <select x-model="form.type" class="input">
              <option value="revenue">Revenue (Sales Amount)</option>
              <option value="quantity">Quantity (Units Sold)</option>
              <option value="new_customers">New Customers</option>
            </select>
          </div>
        </template>

        
        <div>
          <label class="label text-xs">Target Value <span class="text-red-500">*</span></label>
          <div class="relative">
            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-sm text-gray-400 font-medium"
                  x-text="form.type==='revenue'?'Rs':''"></span>
            <input type="number" x-model.number="form.target_value" step="0.01" min="0"
                   class="input" :class="form.type==='revenue'?'pl-9':''"
                   placeholder="0.00" />
          </div>
        </div>

        
        <template x-if="!editTarget && tab==='monthly' && modalAllocInfo">
          <div class="rounded-lg p-2.5 text-xs"
               :style="modalAllocInfo.allocated>modalAllocInfo.annual?'background:#fef2f2;color:#b91c1c;border:1px solid #fecaca':'background:#f0fdf4;color:#166534;border:1px solid #bbf7d0'">
            <div class="font-semibold mb-0.5"
                 x-text="modalAllocInfo.allocated>modalAllocInfo.annual?'⚠ Over-allocated':'📋 Annual allocation'"></div>
            <div x-text="'Annual: '+fmtTv(modalAllocInfo.annual,form.type)+' · Allocated: '+fmtTv(modalAllocInfo.allocated,form.type)+' · Remaining: '+fmtTv(Math.max(0,modalAllocInfo.annual-modalAllocInfo.allocated),form.type)"></div>
          </div>
        </template>

        
        <div>
          <label class="label text-xs">Alert Threshold (%)</label>
          <input type="number" x-model.number="form.alert_threshold_percent" min="0" max="100"
                 class="input" placeholder="e.g. 70" />
          <p class="text-xs text-gray-400 mt-0.5">Alert when achievement falls below this %</p>
        </div>

        
        <div>
          <label class="label text-xs">Notes</label>
          <textarea x-model="form.notes" class="input" rows="2" placeholder="Optional notes…"></textarea>
        </div>

        
        <div x-show="modalError" class="text-sm text-red-600 bg-red-50 dark:bg-red-900/20 rounded-lg p-3" x-text="modalError"></div>
      </div>

      <div class="flex justify-end gap-3 mt-6 pt-4 border-t border-gray-100 dark:border-gray-700/40">
        <button @click="showModal=false" class="btn-secondary">Cancel</button>
        <button @click="submitModal()" class="btn-primary" :disabled="saving">
          <span x-show="saving">Saving…</span>
          <span x-show="!saving" x-text="editTarget?'Save Changes':'Create Target'"></span>
        </button>
      </div>
    </div>
  </div>

  
  <div x-show="showDeleteConfirm" class="fixed inset-0 z-50 flex items-center justify-center p-4"
       x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">
    <div class="absolute inset-0" style="background:rgba(15,23,42,.65)" @click="showDeleteConfirm=false"></div>
    <div class="relative bg-white dark:bg-gray-900 rounded-2xl shadow-2xl w-full max-w-sm p-6 z-10">
      <div class="flex items-center gap-3 mb-4">
        <div class="w-10 h-10 rounded-full bg-red-100 flex items-center justify-center flex-shrink-0">
          <svg class="w-5 h-5 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
          </svg>
        </div>
        <div>
          <p class="font-bold text-gray-800 dark:text-gray-100">Delete Target?</p>
          <p class="text-sm text-gray-500" x-text="(deleteTarget?.branch_name ?? '') + (deleteTarget?.user_name ? ' — '+deleteTarget.user_name : '')"></p>
        </div>
      </div>
      <div class="flex justify-end gap-3 mt-4">
        <button @click="showDeleteConfirm=false" class="btn-secondary">Cancel</button>
        <button @click="doDelete()" class="bg-red-600 hover:bg-red-700 text-white font-semibold px-4 py-2 rounded-lg text-sm transition-colors">Delete</button>
      </div>
    </div>
  </div>

</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
function targetsPage() {
  const user   = JSON.parse(localStorage.getItem('medri_user') || '{}');
  const _admin = !!user.is_super_admin || (user.roles ?? []).includes('super_admin') || (user.roles ?? []).includes('admin');
  const now    = new Date();
  return {
    isAdmin: _admin,
    tab:      'monthly',
    selYear:  now.getFullYear(),
    selMonth: now.getMonth() + 1,
    items:    [],
    loading:  true,
    branches: [],
    users:    [],
    annualItems:       [],
    allMonthlyForYear: [],

    years:  Array.from({length:6}, (_,i) => now.getFullYear() - 1 + i),
    months: [
      {v:1,l:'January'},{v:2,l:'February'},{v:3,l:'March'},{v:4,l:'April'},
      {v:5,l:'May'},{v:6,l:'June'},{v:7,l:'July'},{v:8,l:'August'},
      {v:9,l:'September'},{v:10,l:'October'},{v:11,l:'November'},{v:12,l:'December'},
    ],

    showModal: false, editTarget: null, saving: false, modalError: '',
    showDeleteConfirm: false, deleteTarget: null,
    form: { target_for:'branch', branch_id:'', user_id:'', type:'revenue',
            target_value:0, alert_threshold_percent:70, notes:'' },

    get branchTargets() { return this.items.filter(t => !t.user_id); },
    get repTargets()    { return this.items.filter(t =>  t.user_id); },

    get allocationMap() {
      const map = {};
      for (const t of this.annualItems) {
        const k = t.branch_id + '_' + (t.user_id || '') + '_' + t.type;
        map[k] = { annual: t.target_value, allocated: 0, count: 0 };
      }
      for (const t of this.allMonthlyForYear) {
        const k = t.branch_id + '_' + (t.user_id || '') + '_' + t.type;
        if (map[k]) { map[k].allocated += t.target_value; map[k].count++; }
      }
      return map;
    },

    allocInfo(t) {
      const k = t.branch_id + '_' + (t.user_id || '') + '_' + t.type;
      return this.allocationMap[k] ?? null;
    },

    get modalAllocInfo() {
      if (!this.form.branch_id || !this.form.type || this.tab !== 'monthly') return null;
      const uid = this.form.target_for === 'rep' ? (this.form.user_id || '') : '';
      const k   = this.form.branch_id + '_' + uid + '_' + this.form.type;
      return this.allocationMap[k] ?? null;
    },

    async init() {
      await Promise.all([this.load(), this.loadMeta(), this.loadAllocation()]);
    },

    async load() {
      this.loading = true;
      try {
        let url;
        if (this.tab === 'monthly') {
          url = `/targets?year=${this.selYear}&period=monthly&period_number=${this.selMonth}`;
        } else {
          url = `/targets?year=${this.selYear}&period=annual`;
        }
        const r = await apiFetch(url);
        if (!r) return;
        const raw = await r.json();
        const list = Array.isArray(raw) ? raw : (raw.data ?? []);
        // Calculate pct
        this.items = list.map(t => ({
          ...t,
          branch_name: t.branch?.name ?? t.branch_name ?? '—',
          user_name:   t.user?.name   ?? t.user_name,
          target_value:   parseFloat(t.target_value   ?? 0),
          achieved_value: parseFloat(t.achieved_value ?? 0),
          pct: t.target_value > 0
               ? Math.round((parseFloat(t.achieved_value ?? 0) / parseFloat(t.target_value)) * 100)
               : 0,
        }));
      } finally {
        this.loading = false;
      }
    },

    async loadMeta() {
      if (!this.isAdmin) return;
      try {
        const [br, ur] = await Promise.all([apiFetch('/branches'), apiFetch('/users')]);
        if (br) {
          const bd = await br.json();
          this.branches = (Array.isArray(bd) ? bd : (bd.data ?? [])).map(b => ({ id: b.id, name: b.name }));
        }
        if (ur) {
          const ud = await ur.json();
          this.users = (Array.isArray(ud) ? ud : (ud.data ?? [])).map(u => ({
            id: u.id, name: u.name, branch: u.branch?.name ?? u.default_branch?.name ?? ''
          }));
        }
      } catch(e) {}
    },

    async loadAllocation() {
      try {
        const [ar, mr] = await Promise.all([
          apiFetch(`/targets?year=${this.selYear}&period=annual`),
          apiFetch(`/targets?year=${this.selYear}&period=monthly`),
        ]);
        const parse = async (r) => {
          if (!r) return [];
          const d = await r.json();
          const list = Array.isArray(d) ? d : (d.data ?? []);
          return list.map(t => ({
            branch_id:    parseInt(t.branch?.id ?? t.branch_id ?? 0),
            user_id:      t.user_id ? parseInt(t.user_id) : null,
            type:         t.type,
            target_value: parseFloat(t.target_value ?? 0),
          }));
        };
        this.annualItems       = await parse(ar);
        this.allMonthlyForYear = await parse(mr);
      } catch(e) {}
    },

    openCreate(forType) {
      this.editTarget  = null;
      this.modalError  = '';
      this.form = {
        target_for: forType ?? 'branch',
        branch_id: '', user_id: '', type: 'revenue',
        target_value: 0, alert_threshold_percent: 70, notes: ''
      };
      this.showModal = true;
    },

    openEdit(t) {
      this.editTarget = t;
      this.modalError = '';
      this.form = {
        target_for:              t.user_id ? 'rep' : 'branch',
        branch_id:               t.branch_id,
        user_id:                 t.user_id ?? '',
        type:                    t.type,
        target_value:            t.target_value,
        alert_threshold_percent: t.alert_threshold_percent ?? 70,
        notes:                   t.notes ?? '',
      };
      this.showModal = true;
    },

    async submitModal() {
      this.modalError = '';
      if (!this.form.branch_id)    { this.modalError = 'Please select a branch.'; return; }
      if (this.form.target_for === 'rep' && !this.form.user_id) { this.modalError = 'Please select a sales rep.'; return; }
      if (!this.form.target_value || this.form.target_value <= 0) { this.modalError = 'Target value must be greater than 0.'; return; }

      this.saving = true;
      try {
        let payload, r;
        if (this.editTarget) {
          payload = {
            target_value:            this.form.target_value,
            alert_threshold_percent: this.form.alert_threshold_percent,
            notes:                   this.form.notes,
          };
          r = await apiFetch(`/targets/${this.editTarget.id}`, { method: 'PUT', body: JSON.stringify(payload) });
        } else {
          payload = {
            branch_id:               this.form.branch_id,
            user_id:                 this.form.target_for === 'rep' ? this.form.user_id : null,
            type:                    this.form.type,
            period:                  this.tab === 'annual' ? 'annual' : 'monthly',
            year:                    this.selYear,
            period_number:           this.tab === 'annual' ? 1 : this.selMonth,
            target_value:            this.form.target_value,
            alert_threshold_percent: this.form.alert_threshold_percent,
            notes:                   this.form.notes,
          };
          r = await apiFetch('/targets', { method: 'POST', body: JSON.stringify(payload) });
        }
        if (!r || !r.ok) {
          const err = await r?.json().catch(()=>({}));
          this.modalError = err.message ?? Object.values(err.errors ?? {})[0]?.[0] ?? 'Error saving target.';
          return;
        }
        this.showModal = false;
        await Promise.all([this.load(), this.loadAllocation()]);
        toast(this.editTarget ? 'Target updated.' : 'Target created.', 'success');
      } finally { this.saving = false; }
    },

    confirmDelete(t) { this.deleteTarget = t; this.showDeleteConfirm = true; },
    async doDelete() {
      if (!this.deleteTarget) return;
      const r = await apiFetch(`/targets/${this.deleteTarget.id}`, { method: 'DELETE' });
      if (r?.ok) {
        this.items = this.items.filter(t => t.id !== this.deleteTarget.id);
        toast('Target deleted.', 'success');
        this.loadAllocation();
      } else {
        toast('Failed to delete target.', 'error');
      }
      this.showDeleteConfirm = false;
      this.deleteTarget = null;
    },

    typeLabel(type) {
      return { revenue:'Revenue', quantity:'Quantity', new_customers:'New Customers' }[type] ?? type;
    },
    monthName(m) {
      return ['','Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'][m] ?? '';
    },
    fmtTv(value, type) {
      if (type === 'revenue') return typeof fmtMoney === 'function' ? fmtMoney(value) : 'Rs ' + Number(value).toLocaleString();
      const n = Number(value).toLocaleString('en-US', { maximumFractionDigits: 0 });
      return type === 'quantity' ? n + ' units' : String(n);
    },
  };
}
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/medrilk/system.medri.lk/backend/resources/views/targets/index.blade.php ENDPATH**/ ?>