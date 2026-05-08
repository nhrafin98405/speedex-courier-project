<?php
/** Shared nhr email layout. $title, $heading, $intro, $bodyHtml, $cta (optional) */
$title    = $title    ?? 'nhr Notification';
$heading  = $heading  ?? 'nhr Courier Service';
$intro    = $intro    ?? '';
$bodyHtml = $bodyHtml ?? '';
$cta      = $cta      ?? null; // ['label'=>..., 'url'=>...]
?>
<!DOCTYPE html>
<html><head><meta charset="UTF-8"><title><?= htmlspecialchars($title) ?></title></head>
<body style="margin:0;padding:0;background:#0b1220;font-family:Arial,Helvetica,sans-serif;color:#e5e7eb;">
  <table width="100%" cellpadding="0" cellspacing="0" style="background:#0b1220;padding:32px 0;">
    <tr><td align="center">
      <table width="600" cellpadding="0" cellspacing="0" style="background:#111827;border:1px solid rgba(34,197,94,0.25);border-radius:16px;overflow:hidden;box-shadow:0 20px 60px rgba(34,197,94,0.15);">
        <tr><td style="background:linear-gradient(135deg,#22c55e,#15803d);padding:28px 32px;">
          <table width="100%"><tr>
            <td style="color:#fff;font-size:22px;font-weight:800;letter-spacing:0.5px;">🚚 nhr</td>
            <td align="right" style="color:rgba(255,255,255,0.85);font-size:12px;">Courier Service</td>
          </tr></table>
        </td></tr>
        <tr><td style="padding:32px;">
          <h2 style="color:#22c55e;margin:0 0 16px 0;font-size:20px;"><?= htmlspecialchars($heading) ?></h2>
          <?php if ($intro): ?><p style="color:#cbd5e1;font-size:14px;line-height:1.7;margin:0 0 16px 0;"><?= $intro ?></p><?php endif; ?>
          <?= $bodyHtml ?>
          <?php if ($cta): ?>
          <p style="text-align:center;margin:28px 0 8px;">
            <a href="<?= htmlspecialchars($cta['url']) ?>" style="background:#22c55e;color:#0b1220;text-decoration:none;padding:12px 28px;border-radius:10px;font-weight:700;display:inline-block;">
              <?= htmlspecialchars($cta['label']) ?>
            </a>
          </p>
          <?php endif; ?>
        </td></tr>
        <tr><td style="background:#0b1220;padding:20px 32px;border-top:1px solid rgba(34,197,94,0.2);color:#64748b;font-size:11px;text-align:center;">
          © <?= date('Y') ?> nhr Courier Service · Fast. Safe. Reliable.<br>
          This is an automated message — please do not reply.
        </td></tr>
      </table>
    </td></tr>
  </table>
</body></html>
