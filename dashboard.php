<?php
require __DIR__ . '/app.php';
require_login();
$error = '';
$created = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $building = trim($_POST['building'] ?? '');
    $floor = trim($_POST['floor'] ?? '');
    $phone = trim($_POST['contact_phone'] ?? '');
    $desc = trim($_POST['description'] ?? '');

    if (!$building || !$floor || !$phone || !$desc) {
        $error = 'กรุณากรอกข้อมูลที่มีเครื่องหมาย * ให้ครบ';
    } else {
        $imagePath = null;
        $movedPath = null;
        try {
            if (!empty($_FILES['repair_image']['name'])) {
                if ($_FILES['repair_image']['error'] !== UPLOAD_ERR_OK || $_FILES['repair_image']['size'] > 5 * 1024 * 1024) {
                    throw new RuntimeException('รูปภาพเกิน 5 MB หรืออัปโหลดไม่สำเร็จ');
                }
                $mime = (new finfo(FILEINFO_MIME_TYPE))->file($_FILES['repair_image']['tmp_name']);
                $extensions = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
                if (!isset($extensions[$mime])) throw new RuntimeException('รองรับเฉพาะภาพ JPG, PNG และ WEBP');
                $dir = __DIR__ . '/uploads';
                if (!is_dir($dir) && !mkdir($dir, 0750, true)) throw new RuntimeException('สร้างโฟลเดอร์อัปโหลดไม่ได้');
                $file = bin2hex(random_bytes(18)) . '.' . $extensions[$mime];
                $movedPath = $dir . '/' . $file;
                if (!move_uploaded_file($_FILES['repair_image']['tmp_name'], $movedPath)) throw new RuntimeException('บันทึกรูปภาพไม่สำเร็จ');
                $imagePath = 'uploads/' . $file;
            }

            $conn = db();
            $ticket = new_ticket($conn);
            $uid = (int)$_SESSION['db_user_id'];
            $student = (string)$_SESSION['user_id'];
            $stmt = $conn->prepare('INSERT INTO repair_tickets(ticket_code,user_id,student_id,building,floor,contact_phone,description,image_path,admin_note) VALUES(?,?,?,?,?,?,?,?,?)');
            $empty = '';
            $stmt->bind_param('sisssssss', $ticket, $uid, $student, $building, $floor, $phone, $desc, $imagePath, $empty);
            $stmt->execute();
            $stmt->close();
            $created = $ticket;
        } catch (Throwable $ex) {
            if ($movedPath && is_file($movedPath)) unlink($movedPath);
            $error = $ex instanceof RuntimeException ? $ex->getMessage() : 'ส่งคำขอไม่สำเร็จ กรุณาตรวจสอบฐานข้อมูล';
        }
    }
}

$stmt = db()->prepare('SELECT ticket_code,building,floor,contact_phone,description,image_path,status,admin_note,created_at,updated_at FROM repair_tickets WHERE user_id=? ORDER BY created_at DESC');
$stmt->bind_param('i', $_SESSION['db_user_id']);
$stmt->execute();
$tickets = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();
page_head('แจ้งซ่อมและรายการของฉัน', 'request');
?>
<section class="page-intro">
  <div><p class="eyebrow">STUDENT WORKSPACE</p><h1>แจ้งซ่อมของฉัน</h1><p>ส่งรายละเอียดปัญหาและติดตามสถานะงานของคุณ</p></div>
  <div class="user-chip"><span>R</span><div><b><?=e(trim(($_SESSION['name'] ?? '') . ' ' . ($_SESSION['lastname'] ?? '')))?></b><small>รหัสนักศึกษา <?=e($_SESSION['user_id'])?></small></div></div>
</section>
<div class="workspace">
  <section class="panel request-panel">
    <div class="panel-heading"><div><p class="eyebrow">NEW REQUEST</p><h2>ส่งคำขอแจ้งซ่อม</h2></div><span class="req-count">01</span></div>
    <?php if ($error): ?><div class="alert"><?=e($error)?></div><?php endif; ?>
    <?php if ($created): ?><div class="success-box compact"><b>ส่งคำขอเรียบร้อย</b><p>Ticket ของคุณ <strong><?=e($created)?></strong> เก็บรหัสนี้ไว้ติดตามงาน</p></div><?php endif; ?>
    <form method="post" enctype="multipart/form-data" class="form-stack">
      <?=csrf_field()?>
      <div class="two-col">
        <label>อาคาร / สถานที่ <em>*</em><input name="building" required maxlength="160" value="<?=e($_POST['building'] ?? '')?>" placeholder="เช่น อาคารเรียนรวม"></label>
        <label>ชั้น / ห้อง <em>*</em><input name="floor" required maxlength="100" value="<?=e($_POST['floor'] ?? '')?>" placeholder="เช่น ชั้น 2 ห้อง 204"></label>
      </div>
      <label>เบอร์ติดต่อ <em>*</em><input name="contact_phone" type="tel" required maxlength="40" value="<?=e($_POST['contact_phone'] ?? '')?>" placeholder="เช่น 08X-XXX-XXXX"></label>
      <label>รายละเอียดอาการ <em>*</em><textarea name="description" rows="4" required placeholder="อธิบายอาการที่พบ เช่น เปิดเครื่องไม่ติด หรือหน้าจอแสดงข้อความผิดพลาด"><?=e($_POST['description'] ?? '')?></textarea></label>
      <label class="upload-area" for="repair_image">
        <span class="upload-icon">＋</span><span><b>แนบภาพประกอบ</b><small>JPG, PNG หรือ WEBP · สูงสุด 5 MB</small></span>
        <input id="repair_image" type="file" name="repair_image" accept="image/jpeg,image/png,image/webp">
      </label>
      <div class="image-preview" id="image-preview" hidden>
        <img id="preview-image" src="" alt="ตัวอย่างภาพที่เลือก">
        <div class="preview-caption"><b id="preview-name"></b><small id="preview-size"></small></div>
        <button class="remove-image" id="remove-image" type="button" aria-label="ลบภาพที่เลือก">ลบภาพ</button>
      </div>
      <button class="button">ส่งคำขอแจ้งซ่อม <span>→</span></button>
    </form>
  </section>
  <aside class="side-info">
    <div class="info-card"><span class="info-icon">i</span><h3>เตรียมข้อมูลให้พร้อม</h3><p>ระบุอาคารและห้องให้ชัดเจน พร้อมอธิบายอาการที่พบ เพื่อให้ทีม IT ช่วยตรวจสอบได้รวดเร็วขึ้น</p></div>
    <div class="info-card green"><span class="info-icon">✓</span><h3>ติดตามได้ทุกขั้นตอน</h3><p>หลังส่งคำขอ ระบบจะออก Ticket สำหรับค้นหาสถานะงานของคุณ</p><a href="track.php">ไปหน้าติดตามงาน →</a></div>
  </aside>
</div>
<section class="ticket-section">
  <div class="section-row"><div><p class="eyebrow">REPAIR HISTORY</p><h2>ประวัติการแจ้งซ่อม</h2></div><span class="pill-count"><?=count($tickets)?> รายการ</span></div>
  <?php if (!$tickets): ?><div class="empty-state"><span>⌁</span><h3>ยังไม่มีรายการแจ้งซ่อม</h3><p>ส่งคำขอแรกของคุณได้จากแบบฟอร์มด้านบน</p></div>
  <?php else: ?><div class="ticket-list"><?php foreach ($tickets as $t): ?>
    <article class="ticket-card"><div class="ticket-top"><div><small>TICKET</small><h3><?=e($t['ticket_code'])?></h3></div><span class="status <?=e(status_class($t['status']))?>"><?=e($t['status'])?></span></div>
      <p class="ticket-desc"><?=nl2br(e($t['description']))?></p><div class="ticket-meta"><span>⌖ <?=e($t['building'])?> · <?=e($t['floor'])?> · <?=e($t['contact_phone'])?></span><span><?=e($t['created_at'])?></span></div>
      <?php if ($t['image_path']): ?><a href="<?=e($t['image_path'])?>" target="_blank" rel="noopener"><img class="ticket-image" src="<?=e($t['image_path'])?>" alt="ภาพประกอบการแจ้งซ่อม"></a><?php endif; ?>
      <?php if ($t['admin_note']): ?><div class="admin-note"><b>อัปเดตจากทีม IT</b><p><?=nl2br(e($t['admin_note']))?></p></div><?php endif; ?>
    </article>
  <?php endforeach; ?></div><?php endif; ?>
</section>
<script>
(() => {
  const input = document.getElementById('repair_image');
  const box = document.getElementById('image-preview');
  const img = document.getElementById('preview-image');
  const name = document.getElementById('preview-name');
  const size = document.getElementById('preview-size');
  let previewUrl = null;
  input.addEventListener('change', () => {
    const file = input.files && input.files[0];
    if (previewUrl) URL.revokeObjectURL(previewUrl);
    if (!file) { box.hidden = true; return; }
    if (!['image/jpeg', 'image/png', 'image/webp'].includes(file.type)) {
      input.value = ''; box.hidden = true; alert('เลือกไฟล์ JPG, PNG หรือ WEBP เท่านั้น'); return;
    }
    if (file.size > 5 * 1024 * 1024) {
      input.value = ''; box.hidden = true; alert('ขนาดภาพต้องไม่เกิน 5 MB'); return;
    }
    previewUrl = URL.createObjectURL(file);
    img.src = previewUrl;
    name.textContent = file.name;
    size.textContent = `${(file.size / 1024 / 1024).toFixed(2)} MB`;
    box.hidden = false;
  });
  document.getElementById('remove-image').addEventListener('click', () => {
    input.value = ''; box.hidden = true;
    if (previewUrl) URL.revokeObjectURL(previewUrl);
    previewUrl = null; img.removeAttribute('src');
  });
})();
</script>
<?php page_foot(); ?>
