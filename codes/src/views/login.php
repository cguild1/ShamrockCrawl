<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>QR Admin Login</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-5">
  <div class="row justify-content-center">
    <div class="col-md-5">
      <div class="card shadow-sm">
        <div class="card-body p-4">
          <h1 class="h4 mb-3">QR Admin Login</h1>
          <?php if (isset($_GET['e'])): ?><div class="alert alert-danger">Invalid credentials or not approved.</div><?php endif; ?>
          <form method="post" action="<?= e($codesPath) ?>/admin/login">
            <div class="mb-3"><label class="form-label">Email</label><input class="form-control" type="email" name="email" required></div>
            <div class="mb-3"><label class="form-label">Password</label><input class="form-control" type="password" name="password" required></div>
            <button class="btn btn-primary w-100">Sign in</button>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>
</body>
</html>
