<?php
require __DIR__ . '/app.php';
require_login();
$stmt = db()->prepare('SELECT student_id,name,lastname,department,created_at FROM users WHERE id=?');
$stmt->bind_param('i', $_SESSION['db_user_id']);
$stmt->execute();
$profile = $stmt->get_result()->fetch_assoc();
$stmt->close();
$search = trim($_GET['ticket'] ?? '');
if ($search !== '') {
    $stmt = db()->prepare('SELECT ticket_code,building,floor,contact_phone,description,image_path,status,admin_note,created_at,updated_at FROM repair_tickets WHERE user_id=? AND ticket_code LIKE ? ORDER BY created_at DESC');
    $like = '%' . strtoupper($search) . '%';
    $stmt->bind_param('is', $_SESSION['db_user_id'], $like);
} else {
    $stmt = db()->prepare('SELECT ticket_code,building,floor,contact_phone,description,image_path,status,admin_note,created_at,updated_at FROM repair_tickets WHERE user_id=? ORDER BY created_at DESC');
    $stmt->bind_param('i', $_SESSION['db_user_id']);
}
$stmt->execute();
$tickets = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();
page_head('โปรไฟล์และประวัติแจ้งซ่อม', 'profile');
?>
<section class="page-intro"><div><p class="eyebrow">MY ACCOUNT</p><h1>โปรไฟล์ของฉัน</h1><p>ข้อมูลบัญชีและประวัติการแจ้งซ่อมทั้งหมดของคุณ</p></div><a class="button" href="dashboard.php">＋ แจ้งซ่อมใหม่</a></section>
<section class="profile-card panel">
  <div class="profile-avatar">R</div>
  <div class="profile-info"><p class="eyebrow">STUDENT PROFILE</p><h2><?=e(trim(($profile['name'] ?? '') . ' ' . ($profile['lastname'] ?? '')))?></h2><div class="profile-meta"><span><small>รหัสนักศึกษา</small><b><?=e($profile['student_id'] ?? '')?></b></span><span><small>สาขาวิชา</small><b><?=e($profile['department'] ?? '')?></b></span><span><small>สมาชิกตั้งแต่</small><b><?=e($profile['created_at'] ?? '')?></b></span></div></div>
</section>
<section class="ticket-section profile-history">
  <div class="section-row"><div><p class="eyebrow">YOUR REPAIR HISTORY</p><h2>ประวัติการแจ้งซ่อม</h2><p class="history-hint">Ticket ทั้งหมดของคุณอยู่ที่นี่ เผื่อต้องการค้นหารหัสที่เคยได้รับ</p></div><span class="pill-count"><?=count($tickets)?> รายการ</span></div>
  <form class="history-search" method="get"><label for="ticket-search">ค้นหา Ticket ในประวัติของฉัน</label><div><input id="ticket-search" name="ticket" value="<?=e($search)?>" placeholder="เช่น RIETC-A7K2P"><button class="button">ค้นหา</button><?php if ($search !== ''): ?><a class="button button-light" href="profile.php">ล้าง</a><?php endif; ?></div></form>
  <?php if (!$tickets && $search !== ''): ?><div class="empty-state"><span>⌕</span><h3>ไม่พบ Ticket ที่ค้นหา</h3><p>ลองตรวจสอบรหัส หรือค้นหาด้วย Ticket บางส่วน</p></div>
  <?php elseif (!$tickets): ?><div class="empty-state"><span>⌁</span><h3>ยังไม่มีประวัติการแจ้งซ่อม</h3><p>เมื่อส่งคำขอแล้ว Ticket จะปรากฏในหน้านี้</p><a class="button" href="dashboard.php">แจ้งซ่อมเครื่อง</a></div>
  <?php else: ?><div class="ticket-list"><?php foreach ($tickets as $t): ?>
    <article class="ticket-card profile-ticket">
      <div class="ticket-top"><div><small>TICKET ของคุณ</small><h3><?=e($t['ticket_code'])?></h3></div><span class="status <?=e(status_class($t['status']))?>"><?=e($t['status'])?></span></div>
      <div class="profile-ticket-details"><span><small>สถานที่</small><?=e($t['building'])?> · <?=e($t['floor'])?></span><span><small>วันที่แจ้ง</small><?=e($t['created_at'])?></span></div>
      <p class="ticket-desc"><?=nl2br(e($t['description']))?></p>
      <?php if ($t['admin_note']): ?><div class="admin-note"><b>อัปเดตจากทีม IT</b><p><?=nl2br(e($t['admin_note']))?></p></div><?php endif; ?>
      <div class="profile-actions"><a class="button button-small" href="track.php?ticket=<?=urlencode($t['ticket_code'])?>">ดูความคืบหน้า <span>→</span></a><button class="copy-ticket" type="button" data-ticket="<?=e($t['ticket_code'])?>">คัดลอก Ticket</button></div>
    </article>
  <?php endforeach; ?></div><?php endif; ?>
</section>
<script>
(() => {
  const avatar = document.querySelector('.profile-avatar');
  const info = document.querySelector('.profile-info');
  if (!avatar || !info) return;
  const syncSquare = () => requestAnimationFrame(() => {
    avatar.style.width = `${info.getBoundingClientRect().height}px`;
  });
  new ResizeObserver(syncSquare).observe(info);
  window.addEventListener('load', syncSquare);
})();
document.querySelectorAll('.copy-ticket').forEach(button => button.addEventListener('click', async () => {
  const original = button.textContent;
  try { await navigator.clipboard.writeText(button.dataset.ticket); button.textContent = 'คัดลอกแล้ว'; }
  catch { window.prompt('คัดลอก Ticket นี้', button.dataset.ticket); }
  window.setTimeout(() => { button.textContent = original; }, 1600);
}));
</script>
<?php page_foot(); ?>
