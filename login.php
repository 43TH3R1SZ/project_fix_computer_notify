<?php
require __DIR__ . '/app.php';
if (logged_in()) { header('Location: index.php'); exit; }
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf(); $student = trim($_POST['username'] ?? ''); $pass = $_POST['password'] ?? '';
    try { $stmt=db()->prepare('SELECT id,student_id,name,lastname,role,password FROM users WHERE student_id=?'); $stmt->bind_param('s',$student); $stmt->execute(); $u=$stmt->get_result()->fetch_assoc(); $stmt->close();
        if ($u && password_verify($pass,$u['password'])) { session_regenerate_id(true); $_SESSION['db_user_id']=(int)$u['id']; $_SESSION['user_id']=$u['student_id']; $_SESSION['name']=$u['name']; $_SESSION['lastname']=$u['lastname']; $_SESSION['role']=$u['role']; header('Location: index.php'); exit; }
        $error='รหัสนักศึกษาหรือรหัสผ่านไม่ถูกต้อง';
    } catch (Throwable $e) { $error='เชื่อมต่อฐานข้อมูลไม่ได้ กรุณาตรวจสอบการตั้งค่า'; }
}
page_head('เข้าสู่ระบบ'); ?>
<section class="auth-layout"><div class="auth-aside"><p class="eyebrow">WELCOME BACK</p><h1>กลับมาแล้ว<br>ยินดีต้อนรับ</h1><p>เข้าสู่ระบบเพื่อส่งคำขอซ่อมและดูประวัติการแจ้งซ่อมของคุณ</p><div class="auth-deco">RIETC <b>CARE</b><span>IT SUPPORT PORTAL</span></div></div><div class="auth-card"><div class="auth-heading"><div class="step-icon">↗</div><div><p class="eyebrow">YOUR ACCOUNT</p><h2>เข้าสู่ระบบ</h2></div></div><?php if($error): ?><div class="alert"><?=e($error)?></div><?php endif; ?><form method="post" class="form-stack"><?=csrf_field()?><label>รหัสนักศึกษา<input name="username" required autocomplete="username" placeholder="กรอกรหัสนักศึกษา"></label><label>รหัสผ่าน<input type="password" name="password" required autocomplete="current-password" placeholder="กรอกรหัสผ่าน"></label><button class="button full">เข้าสู่ระบบ <span>→</span></button></form><p class="form-foot">ยังไม่มีบัญชี? <a href="register.php">สมัครสมาชิก</a></p></div></section>
<?php page_foot(); ?>
