<!doctype html>
<html lang="bn">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>MCQ Package Payment Successful | Live Education BD</title>
  <meta http-equiv="refresh" content="4;url={{ $dashboardUrl }}">
  <style>
    *{box-sizing:border-box}
    body{margin:0;min-height:100vh;display:grid;place-items:center;background:#f4f8ff;color:#0c376a;font-family:Arial,'Noto Sans Bengali',sans-serif;padding:18px}
    .panel{width:min(100%,540px);text-align:center;padding:32px 24px;background:#fff;border:1px solid #dce8fa;border-radius:18px;box-shadow:0 12px 36px #16416f18}
    .check{display:grid;place-items:center;margin:0 auto 18px;width:66px;height:66px;border-radius:50%;background:#dcfce7;color:#15803d;font-size:36px;font-weight:bold}
    h1{font-size:24px;margin:0 0 14px}p{line-height:1.7;color:#436080}
    .go{display:block;text-decoration:none;background:#146ede;color:white;border-radius:10px;padding:14px;margin-top:20px;font-weight:700}
    .muted{font-size:13px}
  </style>
</head>
<body>
  <main class="panel" role="status" aria-live="polite">
    <div class="check" aria-hidden="true">✓</div>
    <h1>পেমেন্ট সফল হয়েছে!</h1>
    <p>{{ $notice }}</p>
    <p class="muted">আপনাকে Student Dashboard-এ নিয়ে যাওয়া হচ্ছে…</p>
    <a class="go" href="{{ $dashboardUrl }}">Student Dashboard-এ যান</a>
  </main>
  <script>setTimeout(function(){window.location.replace(@json($dashboardUrl));},4000);</script>
</body>
</html>
