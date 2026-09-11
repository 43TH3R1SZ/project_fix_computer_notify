<?php
session_start();

// ตรวจสอบว่าผู้ใช้ล็อกอินหรือยัง
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// เช็คสิทธิ์ว่าเป็น Admin หรือไม่ (ดึงจาก Session ที่ตั้งไว้ตอน Login)
$is_admin = (isset($_SESSION['role']) && $_SESSION['role'] === 'admin');
?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>หน้าแรก - ยินดีต้อนรับ</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Prompt', sans-serif; }
    </style>
</head>

<body class="bg-gray-50 flex items-center justify-center min-h-screen py-10">
    <div class="w-full max-w-[600px] px-4">
        
        <div class="bg-white border border-gray-200 rounded-lg p-6 md:p-8 shadow-sm text-center">
            
            <div class="mb-6">
                <!-- ไอคอนจำลอง -->
                <div class="w-24 h-24 bg-gray-200 rounded-full mx-auto flex items-center justify-center mb-4 relative">
                    <svg class="w-12 h-12 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"></path>
                    </svg>
                    <!-- โชว์ป้าย Admin เล็กๆ ตรงรูปโปรไฟล์ถ้าเป็นแอดมิน -->
                    <?php if ($is_admin): ?>
                        <span class="absolute bottom-0 right-0 bg-blue-500 text-white text-[10px] font-bold px-2 py-1 rounded-full border-2 border-white">ADMIN</span>
                    <?php endif; ?>
                </div>
                <h1 class="text-2xl font-semibold text-gray-800 mb-2">ยินดีต้อนรับเข้าสู่ระบบ</h1>
                <p class="text-gray-500">คุณได้เข้าสู่ระบบสำเร็จแล้ว</p>
            </div>

            <!-- แสดงข้อมูลจาก Session -->
            <div class="bg-gray-50 rounded-md p-4 mb-8 text-left border border-gray-100">
                <h2 class="text-lg font-medium text-gray-700 border-b pb-2 mb-3">ข้อมูลผู้ใช้งาน</h2>
                
                <div class="grid grid-cols-3 gap-2 text-sm mb-2">
                    <span class="text-gray-500 col-span-1">ชื่อ-นามสกุล:</span>
                    <span class="text-gray-800 font-medium col-span-2">
                        <?php echo htmlspecialchars($_SESSION['name']) . " " . htmlspecialchars($_SESSION['lastname'] ?? ''); ?>
                    </span>
                </div>
                
                <div class="grid grid-cols-3 gap-2 text-sm">
                    <span class="text-gray-500 col-span-1">รหัสนักศึกษา:</span>
                    <span class="text-gray-800 font-medium col-span-2">
                        <?php echo htmlspecialchars($_SESSION['user_id']); ?>
                    </span>
                </div>
            </div>

            <!-- กลุ่มปุ่มเมนู -->
            <div class="flex flex-col gap-3">
                
                <!-- ปุ่มแจ้งซ่อม (แสดงให้ทุกคนเห็น) -->
                <a href="dashboard.php" class="w-full bg-[#F0441C] hover:bg-[#D93A16] text-white font-medium py-2.5 px-4 rounded-md transition duration-200 shadow-sm">
                    เข้าสู่หน้าแจ้งซ่อม (IT Support)
                </a>

                <!-- ปุ่มจัดการสำหรับแอดมิน (แสดงเฉพาะ Admin) -->
                <?php if ($is_admin): ?>
                    <a href="staff_dashboard.php" class="w-full bg-gray-900 hover:bg-gray-800 text-white font-medium py-2.5 px-4 rounded-md transition duration-200 shadow-sm flex items-center justify-center gap-2">
                        <svg class="w-5 h-5 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                        จัดการตั๋วแจ้งซ่อม (Staff Dashboard)
                    </a>
                <?php endif; ?>
                
                <!-- ปุ่มออกจากระบบ -->
                <a href="logout.php" class="w-full bg-red-50 text-red-600 hover:bg-red-100 hover:text-red-700 font-medium py-2.5 px-4 rounded-md transition duration-200 border border-red-200 mt-2">
                    ออกจากระบบ (Logout)
                </a>
            </div>
            
        </div>
    </div>
</body>
</html>