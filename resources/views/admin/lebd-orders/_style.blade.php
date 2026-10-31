<style>
.lebd-orders-page{max-width:1500px;margin:24px auto;padding:0 16px 40px;color:#10274a;font-family:inherit}
.lebd-orders-top{display:flex;justify-content:space-between;align-items:end;gap:12px;margin-bottom:19px}.lebd-orders-top h1{font-size:26px;font-weight:800;margin:0}.lebd-orders-top small{color:#7184a0}
.lebd-orders-panel{background:#fff;border:1px solid #e4eaf4;border-radius:15px;box-shadow:0 6px 24px #133e6410;padding:20px;margin-bottom:18px}
.lebd-orders-heading{font-size:18px;font-weight:800;margin:0 0 16px}.lebd-orders-tiles{display:grid;grid-template-columns:repeat(6,minmax(0,1fr));gap:11px}
.lebd-orders-tile{text-decoration:none!important;border:1px solid #e4e9f2;border-radius:12px;min-height:124px;display:flex;align-items:center;justify-content:center;flex-direction:column;gap:9px;text-align:center;color:#152b4c;background:#f8faff;padding:13px 5px;font-weight:750;font-size:13px}
.lebd-orders-tile span{width:44px;height:44px;border-radius:12px;display:grid;place-items:center;background:#e2efff;color:#0b73e5;font-size:23px}.lebd-orders-tile.active{border-color:#2885ff;background:#edf6ff;color:#0866cc}.lebd-orders-tile.disabled{opacity:.55;cursor:not-allowed}.lebd-orders-tile small{font-size:10px;font-weight:600;color:#798ba0}
.lebd-orders-filter{display:flex;gap:10px;flex-wrap:wrap;margin-bottom:14px}.lebd-orders-filter input,.lebd-orders-filter select{border:1px solid #d7e1ef;border-radius:8px;padding:10px;min-height:42px;background:#fff;color:#1c344f;max-width:100%}.lebd-orders-filter input[type=search]{flex:2;min-width:190px}.lebd-orders-filter button{background:#096bdb;border:0;border-radius:8px;color:#fff;padding:10px 22px;font-weight:700}
.lebd-orders-table-wrap{overflow:auto}.lebd-orders-table{width:100%;border-collapse:collapse;font-size:13px;white-space:nowrap}.lebd-orders-table th{background:#edf2f8;color:#243851;text-align:left;padding:13px}.lebd-orders-table td{padding:14px 13px;border-bottom:1px solid #e8edf4}.lebd-orders-muted{color:#77889d;font-size:12px}.lebd-orders-status{border-radius:7px;padding:5px 10px;background:#fff2d3;color:#875000;font-weight:700;text-transform:capitalize}.lebd-orders-status.paid{background:#dcfae8;color:#007d42}.lebd-orders-status.failed,.lebd-orders-status.cancelled{background:#ffe2e3;color:#ac2228}
.lebd-orders-action{border:1px solid #197bf1;color:#096bda;border-radius:7px;padding:6px 11px;text-decoration:none}.lebd-orders-empty{padding:28px;text-align:center;color:#647994;background:#f6faff;border-radius:10px}.lebd-orders-summary{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:12px}.lebd-orders-summary div{border:1px solid #e8edf5;border-radius:9px;padding:13px;overflow-wrap:anywhere}.lebd-orders-summary dt{font-size:12px;color:#64788d}.lebd-orders-summary dd{font-weight:750;margin:6px 0 0}
@media(max-width:1100px){.lebd-orders-tiles{grid-template-columns:repeat(3,minmax(0,1fr))}}@media(max-width:550px){.lebd-orders-tiles{grid-template-columns:repeat(2,minmax(0,1fr))}.lebd-orders-panel{padding:12px}.lebd-orders-summary{grid-template-columns:1fr}}

.lebd-video-order-toolbar{display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;margin-bottom:15px}
.lebd-order-tabs{display:flex;gap:9px}
.lebd-order-tabs a{border:1px solid #bdd4f5;background:#fff;color:#16559f;text-decoration:none;padding:10px 28px;border-radius:9px;font-weight:800}
.lebd-order-tabs a.selected{background:#0875df;border-color:#0875df;color:#fff}
.lebd-source-label{color:#19467a;font-size:13px;font-weight:750;background:#f0f6ff;padding:9px 13px;border-radius:9px}
.lebd-orders-dates{align-items:center}.lebd-orders-dates label{font-weight:700;font-size:13px}
.lebd-orders-dates button.lebd-all-outline{background:#fff;color:#096bdb;border:1px solid #c4daf8}
.lebd-orders-dates button.lebd-all-active{background:#12568d}
.lebd-orders-report-note{font-size:12px;color:#637b98;margin:0 0 13px}
.lebd-orders-report-total{display:flex;flex-wrap:wrap;gap:15px;justify-content:flex-end;border-top:2px solid #e5edf8;margin-top:14px;padding-top:16px}
.lebd-orders-report-total div{background:#f4f9ff;border:1px solid #d9e7fb;border-radius:11px;min-width:220px;padding:14px}
.lebd-orders-report-total span{display:block;color:#657b95;font-size:12px}
.lebd-orders-report-total strong{display:block;color:#006fb0;font-size:23px;margin-top:4px}
.lebd-orders-source-switch{margin-top:16px}.lebd-orders-source-switch a{margin:0 8px 8px 0;display:inline-block;background:#eaf3ff;border:1px solid #a5cdfb;color:#075ab5;padding:12px 18px;border-radius:9px;text-decoration:none;font-weight:750}
@media(max-width:600px){.lebd-orders-report-total div{width:100%}.lebd-order-tabs a{padding:10px 20px}}
.lebd-all-dates-link{display:inline-flex;align-items:center;justify-content:center;padding:10px 18px;border:1px solid #c4daf8;border-radius:8px;text-decoration:none;color:#096bdb;font-weight:700;background:#fff}.lebd-all-dates-link.lebd-all-active{background:#12568d;color:#fff}

/* LEBD compact admin orders: protect top heading from sticky public header. */
.lebd-orders-compact-page{padding-top:16px!important;margin-top:0!important;max-width:1600px!important;padding-bottom:26px!important}
.lebd-orders-compact-page .lebd-orders-top{margin-bottom:8px!important}
.lebd-orders-compact-page .lebd-orders-top h1{font-size:22px!important;line-height:1.25}
.lebd-orders-compact-page .lebd-orders-top small{font-size:11px}
.lebd-orders-compact-page .lebd-orders-panel{padding:12px 14px!important;margin-bottom:10px!important;border-radius:12px}
.lebd-orders-compact-page .lebd-orders-heading{font-size:16px!important;margin-bottom:10px!important}
.lebd-orders-compact-page .lebd-orders-tiles{gap:7px!important}
.lebd-orders-compact-page .lebd-orders-tile{min-height:73px!important;padding:6px 3px!important;gap:3px!important;font-size:11px!important;border-radius:9px!important}
.lebd-orders-compact-page .lebd-orders-tile span{width:29px;height:29px;font-size:16px;border-radius:8px}
.lebd-orders-compact-page .lebd-orders-tile small{font-size:9px!important}
.lebd-orders-video-compact .lebd-video-order-toolbar{margin-bottom:8px!important;gap:7px}
.lebd-orders-video-compact .lebd-order-tabs{gap:6px}
.lebd-orders-video-compact .lebd-order-tabs a{padding:6px 13px!important;font-size:12px!important;border-radius:7px}
.lebd-orders-video-compact .lebd-source-label{padding:6px 9px!important;font-size:11px}
.lebd-orders-video-compact .lebd-orders-filter{margin-bottom:7px!important;gap:7px!important}
.lebd-orders-video-compact .lebd-orders-filter input,.lebd-orders-video-compact .lebd-orders-filter button,.lebd-orders-video-compact .lebd-all-dates-link{min-height:32px!important;padding:5px 10px!important;font-size:12px!important}
.lebd-orders-video-compact .lebd-orders-report-note{margin:3px 0 7px!important;font-size:11px!important}
.lebd-orders-video-compact .lebd-orders-table{font-size:11px!important;white-space:normal!important;table-layout:auto!important;min-width:1030px}
.lebd-orders-video-compact .lebd-orders-table th{padding:7px 7px!important;white-space:nowrap;font-size:10px}
.lebd-orders-video-compact .lebd-orders-table td{padding:7px 7px!important;line-height:1.35;vertical-align:middle}
.lebd-orders-video-compact .lebd-orders-table td.lebd-order-id{max-width:145px;overflow-wrap:anywhere;font-size:10px}
.lebd-orders-video-compact .lebd-orders-muted{font-size:10px!important;overflow-wrap:anywhere}
.lebd-orders-video-compact .lebd-orders-status{padding:4px 7px!important;font-size:10px}
.lebd-orders-video-compact .lebd-orders-action{padding:4px 7px!important;font-size:11px}
.lebd-orders-video-compact .lebd-orders-report-total{margin-top:7px!important;padding-top:8px!important;gap:7px}
.lebd-orders-video-compact .lebd-orders-report-total div{padding:7px 10px!important;min-width:160px}
.lebd-orders-video-compact .lebd-orders-report-total strong{font-size:16px!important;margin-top:2px}
.lebd-orders-video-compact .lebd-orders-source-switch{margin-top:8px!important}
.lebd-orders-video-compact .lebd-orders-source-switch a{padding:6px 9px!important;font-size:11px!important;margin:0 5px 5px 0!important}
.lebd-orders-video-compact .lebd-orders-pagination{margin-top:7px}
.lebd-orders-video-compact .lebd-orders-pagination nav{font-size:12px}
@media(max-width:1000px){.lebd-orders-compact-page{padding-top:16px!important}.lebd-orders-video-compact .lebd-orders-table-wrap{overflow-x:auto}}
@media(max-width:600px){.lebd-orders-compact-page{padding-top:12px!important;padding-left:8px!important;padding-right:8px!important}.lebd-orders-compact-page .lebd-orders-tile{min-height:66px!important}.lebd-orders-compact-page .lebd-orders-panel{padding:9px!important}}

/* LEBD-05: deep-toned navigation tiles, admin orders only. */
.lebd-orders-compact-page .lebd-orders-tile:not(.disabled){
 background:linear-gradient(155deg,#0d3270,#1458ac)!important;
 border-color:#173d80!important;color:#fff!important;
 box-shadow:0 3px 9px rgba(9,46,106,.14);
}
.lebd-orders-compact-page .lebd-orders-tile:not(.disabled) span{
 color:#fff!important;background:rgba(255,255,255,.14)!important;
}
.lebd-orders-compact-page .lebd-orders-tile:not(.disabled):hover,
.lebd-orders-compact-page .lebd-orders-tile.active{
 background:linear-gradient(150deg,#0753bb,#063a87)!important;
 border-color:#0252bb!important;color:#fff!important;
}
.lebd-orders-compact-page .lebd-orders-tile.disabled{
 background:#e8edf5!important;border-color:#bdcbe0!important;
 color:#495c77!important;opacity:1!important;
}
.lebd-orders-compact-page .lebd-orders-tile.disabled span{
 background:#cfdcf0!important;color:#3d6296!important;
}
.lebd-orders-compact-page .lebd-orders-tile.disabled small{color:#6a7b96!important}
.lebd-orders-compact-page .lebd-orders-top{margin-top:0!important}
</style>