<style>
/* LEBD-05: all six Order Navigation buttons — light blue + clearly selected. */
.lebd-orders-panel .lebd-orders-tiles{display:grid!important;grid-template-columns:repeat(6,minmax(0,1fr))!important;gap:8px!important;align-items:stretch!important}
.lebd-orders-panel .lebd-orders-tiles>a.lebd-orders-tile{box-sizing:border-box!important;display:flex!important;flex-direction:column!important;justify-content:center!important;align-items:center!important;gap:5px!important;min-width:0!important;min-height:74px!important;margin:0!important;padding:10px 5px!important;border-radius:9px!important;background:#e2efff!important;border:1px solid #a8caff!important;box-shadow:0 2px 7px rgba(22,85,170,.09)!important;color:#174577!important;text-align:center!important;text-decoration:none!important;font-size:11px!important;line-height:1.3!important;font-weight:800!important;transition:background .15s,border-color .15s,box-shadow .15s!important}
.lebd-orders-panel .lebd-orders-tiles>a.lebd-orders-tile span{display:flex!important;align-items:center!important;justify-content:center!important;width:29px!important;height:29px!important;background:#c5ddff!important;color:#1858ad!important;border-radius:8px!important;font-size:16px!important;line-height:1!important}
.lebd-orders-panel .lebd-orders-tiles>a.lebd-orders-tile:hover:not(.active){background:#cde3ff!important;border-color:#76aff7!important}
.lebd-orders-panel .lebd-orders-tiles>a.lebd-orders-tile.active,.lebd-orders-panel .lebd-orders-tiles>a.lebd-orders-tile[aria-current=page]{background:linear-gradient(120deg,#1878e8,#0957c3)!important;border:2px solid #0046aa!important;color:#fff!important;box-shadow:0 0 0 3px rgba(13,112,232,.22),0 6px 14px rgba(10,81,170,.20)!important}
.lebd-orders-panel .lebd-orders-tiles>a.lebd-orders-tile.active span,.lebd-orders-panel .lebd-orders-tiles>a.lebd-orders-tile[aria-current=page] span{background:rgba(255,255,255,.22)!important;color:#fff!important}
.lebd-orders-panel .lebd-orders-tiles>a.lebd-orders-tile:focus-visible{outline:3px solid #ffbf47!important;outline-offset:2px!important}
@media(max-width:950px){.lebd-orders-panel .lebd-orders-tiles{grid-template-columns:repeat(3,minmax(0,1fr))!important}}
@media(max-width:530px){.lebd-orders-panel .lebd-orders-tiles{grid-template-columns:repeat(2,minmax(0,1fr))!important;gap:6px!important}.lebd-orders-panel .lebd-orders-tiles>a.lebd-orders-tile{min-height:66px!important;font-size:10px!important}}
</style>
<div class="lebd-orders-panel">
 <h2 class="lebd-orders-heading">🛒 Order Sections</h2>
 <div class="lebd-orders-tiles">
  <a class="lebd-orders-tile {{ request()->routeIs('admin.lebd-orders.overview') ? 'active' : '' }}" href="{{ route('admin.lebd-orders.overview') }}"><span>▦</span>Overview All Package</a>
  <a class="lebd-orders-tile {{ ($section ?? '') === 'batch' ? 'active' : '' }}" href="{{ route('admin.lebd-orders.batch') }}"><span>🎓</span>Batch Order</a>
  <a class="lebd-orders-tile {{ ($section ?? '') === 'video' ? 'active' : '' }}" href="{{ route('admin.lebd-orders.video') }}"><span>▶</span>Video Order</a>
  <a class="lebd-orders-tile {{ ($section ?? '') === 'ebook' || request()->routeIs('admin.lebd-orders.ebook') ? 'active' : '' }}" href="{{ route('admin.lebd-orders.ebook') }}"><span>📖</span>E-Book Order</a>
  <a class="lebd-orders-tile {{ request()->routeIs('admin.lebd.mcq-orders.*') ? 'active' : '' }}" href="{{ route('admin.lebd.mcq-orders.index') }}"><span>▣</span>MCQ Study Order</a>
  <a class="lebd-orders-tile {{ request()->routeIs('admin.lebd-orders.exam*') ? 'active' : '' }}" href="{{ route('admin.lebd-orders.exam') }}"><span>▤</span>Exam Section Order</a>
 </div>
</div>
