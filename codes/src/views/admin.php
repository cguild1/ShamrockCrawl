<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>QR Admin</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://kit.fontawesome.com/YOUR_KIT_ID.js" crossorigin="anonymous"></script>
</head>
<body>
<nav class="navbar navbar-dark bg-dark">
  <div class="container-fluid"><span class="navbar-brand">GL Parade QR Manager</span><a href="<?= e($codesPath) ?>/admin/logout" class="btn btn-outline-light btn-sm">Logout</a></div>
</nav>
<div class="container py-4">
  <div class="row g-4">
    <div class="col-lg-5">
      <div class="card">
        <div class="card-body">
          <h2 class="h5"><?= $edit ? 'Edit QR Link' : 'Create QR Link' ?></h2>
          <?php if (isset($_GET['error']) && $_GET['error'] === 'slug'): ?><div class="alert alert-danger">Slug already exists.</div><?php endif; ?>
          <form method="post" action="<?= e($codesPath) ?>/admin/save" id="linkForm">
            <input type="hidden" name="id" value="<?= (int)($edit['id'] ?? 0) ?>">
            <div class="mb-2"><label class="form-label">Title</label><input class="form-control" name="title" required value="<?= e((string)($edit['title'] ?? '')) ?>"></div>
            <div class="mb-2"><label class="form-label">Business (optional)</label>
              <select class="form-select" name="business_id"><option value="">-- None --</option><?php foreach ($businesses as $biz): ?><option value="<?= (int)$biz['id'] ?>" <?= ((int)($edit['business_id'] ?? 0) === (int)$biz['id']) ? 'selected' : '' ?>><?= e($biz['business_name']) ?></option><?php endforeach; ?></select>
            </div>
            <div class="mb-2"><label class="form-label">Destination URL</label><input class="form-control" type="url" name="destination_url" required value="<?= e((string)($edit['destination_url'] ?? 'https://')) ?>"></div>
            <div class="mb-2"><label class="form-label">Short Slug</label><input class="form-control" name="slug" id="slug" required value="<?= e((string)($edit['slug'] ?? '')) ?>"><small id="slugStatus" class="text-muted"></small></div>
            <div class="mb-2"><label class="form-label">QR Payload (leave blank to match destination)</label><textarea class="form-control" name="qr_payload" rows="2"><?= e((string)($edit['qr_payload'] ?? '')) ?></textarea></div>

            <?php $settings = json_decode((string)($edit['qr_settings_json'] ?? '{}'), true) ?: []; ?>
            <div class="row g-2">
              <div class="col-6"><label class="form-label">Size</label><input class="form-control" type="number" name="size" value="<?= (int)($settings['size'] ?? 600) ?>"></div>
              <div class="col-6"><label class="form-label">Margin</label><input class="form-control" type="number" name="margin" value="<?= (int)($settings['margin'] ?? 10) ?>"></div>
              <div class="col-6"><label class="form-label">FG Color</label><input class="form-control form-control-color" type="color" name="foreground" value="<?= e((string)($settings['foreground'] ?? '#000000')) ?>"></div>
              <div class="col-6"><label class="form-label">BG Color</label><input class="form-control form-control-color" type="color" name="background" value="<?= e((string)($settings['background'] ?? '#ffffff')) ?>"></div>
              <div class="col-6"><label class="form-label">Format</label><select class="form-select" name="format"><option value="png">PNG</option><option value="svg" <?= (($settings['format'] ?? '') === 'svg') ? 'selected' : '' ?>>SVG</option></select></div>
              <div class="col-6"><label class="form-label">Error Correction</label><select class="form-select" name="error_correction"><?php foreach (['L','M','Q','H'] as $ec): ?><option value="<?= $ec ?>" <?= (($settings['error_correction'] ?? 'M') === $ec) ? 'selected' : '' ?>><?= $ec ?></option><?php endforeach; ?></select></div>
            </div>

            <div class="form-check my-3"><input class="form-check-input" type="checkbox" name="is_active" <?= (($edit['is_active'] ?? 1) ? 'checked' : '') ?>><label class="form-check-label">Active</label></div>
            <button class="btn btn-primary">Save QR Link</button>
          </form>
        </div>
      </div>
    </div>
    <div class="col-lg-7">
      <div class="card">
        <div class="card-body">
          <h2 class="h5">Existing Links</h2>
          <div class="table-responsive">
            <table class="table table-sm align-middle">
              <thead><tr><th>Slug</th><th>Destination</th><th>Scans</th><th>QR</th><th></th></tr></thead>
              <tbody>
                <?php foreach ($all as $row): ?>
                  <tr>
                    <td><a href="/<?= e($row['slug']) ?>" target="_blank"><?= e($row['slug']) ?></a></td>
                    <td class="text-truncate" style="max-width:220px;"><?= e($row['destination_url']) ?></td>
                    <td><?= (int)$row['scan_count'] ?></td>
                    <td><a class="btn btn-outline-secondary btn-sm" href="<?= e($codesPath) ?>/admin/qr-image?id=<?= (int)$row['id'] ?>" target="_blank">View</a></td>
                    <td><a class="btn btn-outline-primary btn-sm" href="<?= e($codesPath) ?>/admin?edit=<?= (int)$row['id'] ?>">Edit</a></td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<script>
const slugInput = document.getElementById('slug');
const slugStatus = document.getElementById('slugStatus');
const editId = <?= (int)($edit['id'] ?? 0) ?>;
let timer;
slugInput?.addEventListener('input', () => {
  clearTimeout(timer);
  timer = setTimeout(async () => {
    const r = await fetch(`<?= e($codesPath) ?>/admin/api/slug-check?slug=${encodeURIComponent(slugInput.value)}&id=${editId}`);
    const d = await r.json();
    slugInput.value = d.slug;
    slugStatus.textContent = d.available ? 'Slug available' : 'Slug already used';
    slugStatus.className = d.available ? 'text-success' : 'text-danger';
  }, 250);
});
</script>
</body>
</html>
