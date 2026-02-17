<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Redirecting...</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-5 text-center">
  <h1 class="h4 mb-3">One moment...</h1>
  <p>We are securely redirecting you to your destination.</p>
  <a class="btn btn-primary" href="/go/<?= e($link['slug']) ?>" id="goNow">Continue</a>
</div>
<script>
(async function () {
  const payload = {
    tz: Intl.DateTimeFormat().resolvedOptions().timeZone,
    lang: navigator.language,
    platform: navigator.platform,
    screen: `${screen.width}x${screen.height}`
  };
  try {
    navigator.sendBeacon('<?= e($codesPath) ?>/track.php?slug=<?= e($link['slug']) ?>', new Blob([JSON.stringify(payload)], {type: 'application/json'}));
  } catch (e) {}
  setTimeout(() => window.location.href = '/go/<?= e($link['slug']) ?>', 500);
})();
</script>
</body>
</html>
