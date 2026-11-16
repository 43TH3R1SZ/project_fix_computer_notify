<?php
declare(strict_types=1);

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_set_cookie_params(['httponly' => true, 'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off', 'samesite' => 'Lax']);
    session_start();
}

function db(): mysqli {
    static $conn;
    if (!$conn) {
        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
        $conn = new mysqli(getenv('DB_HOST') ?: 'localhost', getenv('DB_USER') ?: 'root', getenv('DB_PASS') ?: '', getenv('DB_NAME') ?: 'database_fcn', (int)(getenv('DB_PORT') ?: 3306));
        $conn->set_charset('utf8mb4');
    }
    return $conn;
}
function e(?string $s): string { return htmlspecialchars($s ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }
function logged_in(): bool { return isset($_SESSION['user_id']); }
function is_admin(): bool { return ($_SESSION['role'] ?? '') === 'admin'; }
function status_class(string $status): string { return match ($status) { 'กำลังดำเนินการ' => 'status-active', 'เสร็จสิ้น' => 'status-done', 'ยกเลิก' => 'status-cancelled', default => 'status-pending' }; }
function require_login(): void { if (!logged_in()) { header('Location: login.php'); exit; } }
function require_admin(): void { require_login(); if (!is_admin()) { http_response_code(403); exit('ไม่มีสิทธิ์เข้าถึงหน้านี้'); } }
function csrf_token(): string { if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(32)); return $_SESSION['csrf']; }
function csrf_field(): string { return '<input type="hidden" name="csrf" value="' . e(csrf_token()) . '">'; }
function verify_csrf(): void { if (!hash_equals($_SESSION['csrf'] ?? '', $_POST['csrf'] ?? '')) { http_response_code(400); exit('คำขอหมดอายุ กรุณาโหลดหน้าใหม่แล้วลองอีกครั้ง'); } }
function new_ticket(mysqli $conn): string {
    $alphabet = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
    for ($attempt = 0; $attempt < 30; $attempt++) {
        $suffix = ''; for ($i = 0; $i < 5; $i++) $suffix .= $alphabet[random_int(0, strlen($alphabet) - 1)];
        $code = 'RIETC-' . $suffix;
        $stmt = $conn->prepare('SELECT id FROM repair_tickets WHERE ticket_code=?'); $stmt->bind_param('s', $code); $stmt->execute(); $exists = $stmt->get_result()->fetch_row(); $stmt->close();
        if (!$exists) return $code;
    }
    throw new RuntimeException('สร้าง Ticket ไม่สำเร็จ กรุณาลองใหม่');
}
function page_head(string $title, string $active = ''): void {
    $name = trim(($_SESSION['name'] ?? '') . ' ' . ($_SESSION['lastname'] ?? ''));
    echo '<!doctype html><html lang="th"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><meta name="theme-color" content="#fffaf6"><title>' . e($title) . ' · RIETC Care</title><link rel="stylesheet" href="style.css"><link rel="stylesheet" href="theme.css"></head><body><header class="topbar"><a class="brand" href="index.php"><span class="brand-mark">R</span><span>RIETC <small>CARE</small></span></a><nav><a class="' . ($active==='home'?'active':'') . '" href="index.php">หน้าหลัก</a><a class="' . ($active==='track'?'active':'') . '" href="track.php">ติดตามงาน</a>';
    if (logged_in()) { echo '<a class="' . ($active==='request'?'active':'') . '" href="dashboard.php">แจ้งซ่อม</a>'; if (is_admin()) echo '<a class="' . ($active==='staff'?'active':'') . '" href="staff_dashboard.php">จัดการงาน</a>'; echo '<a class="nav-user profile-link ' . ($active==='profile'?'active':'') . '" href="profile.php" title="เปิดโปรไฟล์และประวัติแจ้งซ่อม">' . e($name) . '</a><a href="logout.php">ออกจากระบบ</a>'; }
    else echo '<a href="login.php">เข้าสู่ระบบ</a><a class="nav-join" href="register.php">สมัครสมาชิก</a>';
    echo '</nav></header><main class="wrap">';
}
function page_foot(): void { echo '</main><footer><span>RIETC CARE</span><span>Computer support, made simple.</span></footer></body></html>'; }
