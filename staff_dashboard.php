<?php
session_start();

if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit();
}

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "database_fcn";

$conn = @new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die('เชื่อมต่อฐานข้อมูลไม่ได้: ' . $conn->connect_error);
}
$conn->set_charset("utf8mb4");

$search_student_id = '';
$result = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_status') {
    $ticket_id = $_POST['ticket_id'];
    $new_status = $_POST['status'];
    $search_return = $_POST['search_student_id'] ?? '';
    
    $stmt = $conn->prepare("UPDATE repair_tickets SET status = ? WHERE id = ?");
    if ($stmt) {
        $stmt->bind_param("si", $new_status, $ticket_id);
        $stmt->execute();
        $stmt->close();
    }
    header("Location: staff_dashboard.php?search=" . urlencode($search_return));
    exit();
}

if (isset($_GET['search']) && trim($_GET['search']) !== '') {
    $search_student_id = trim($_GET['search']);
    $search_ticket_id = 'ticket_' . $search_student_id;

    $stmt = $conn->prepare("SELECT * FROM repair_tickets WHERE student_id = ? ORDER BY created_at DESC");
    if ($stmt) {
        $stmt->bind_param("s", $search_ticket_id);
        $stmt->execute();
        $result = $stmt->get_result();
    }
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ระบบค้นหาข้อมูลแจ้งซ่อม (Staff)</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Prompt', sans-serif; }
        input[type="number"]::-webkit-outer-spin-button,
        input[type="number"]::-webkit-inner-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }
        input[type="number"] {
            appearance: textfield;
        }
    </style>
</head>
<body class="bg-gray-50 min-h-screen">

    <!-- เปลี่ยน Navbar ให้สว่างและใช้โทนสีเดียวกับหน้าผู้ใช้ -->
    <nav class="bg-white shadow-sm border-b border-gray-200 px-6 py-4 mb-8">
        <div class="max-w-7xl mx-auto flex flex-wrap justify-between items-center gap-4">
            <h1 class="text-xl font-bold text-gray-800 flex items-center gap-2">
                <svg class="w-6 h-6 text-[#F0441C]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                ระบบค้นหาประวัติแจ้งซ่อม
            </h1>
            <div>
                <a href="dashboard.php" class="text-sm bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2 rounded-md transition border border-gray-200 font-medium shadow-sm">ไปหน้าแจ้งซ่อม</a>
            </div>
        </div>
    </nav>

    <div class="max-w-7xl mx-auto px-4 pb-10">
        
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-8 max-w-xl mx-auto">
            <h2 class="text-lg font-semibold text-gray-800 mb-4 text-center">ค้นหารายการแจ้งซ่อมของนักศึกษา</h2>
            <form method="GET" action="" class="flex gap-3">
                <input type="number" name="search" value="<?php echo htmlspecialchars($search_student_id); ?>" placeholder="กรอกรหัสนักศึกษา (ตัวเลขเท่านั้น)" class="w-full px-4 py-3 text-gray-700 border border-gray-300 rounded-md focus:outline-none focus:border-[#F0441C] focus:ring-1 focus:ring-[#F0441C] transition" required>
                <!-- ปุ่มค้นหาโทนสีหลัก -->
                <button type="submit" class="bg-[#F0441C] hover:bg-[#D93A16] text-white font-medium px-6 py-3 rounded-md transition whitespace-nowrap shadow-sm">
                    ค้นหา
                </button>
            </form>
        </div>

        <?php if (isset($_GET['search'])): ?>
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                <div class="p-6 border-b border-gray-200 bg-gray-50 flex justify-between items-center">
                    <div>
                        <h2 class="text-lg font-semibold text-gray-800">ผลการค้นหา</h2>
                        <p class="text-sm text-gray-500">รหัสนักศึกษา: <span class="font-semibold text-gray-700"><?php echo htmlspecialchars($search_student_id); ?></span></p>
                    </div>
                    <?php if ($result && $result->num_rows > 0): ?>
                        <!-- เปลี่ยนป้ายแสดงจำนวนรายการเป็นโทนสีหลัก -->
                        <span class="bg-orange-50 text-[#F0441C] text-xs font-semibold px-3 py-1.5 rounded-full border border-orange-200">พบ <?php echo $result->num_rows; ?> รายการ</span>
                    <?php endif; ?>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-white text-gray-600 text-sm border-b border-gray-200">
                                <th class="py-3 px-4 font-medium">รหัส Ticket / วันที่แจ้ง</th>
                                <th class="py-3 px-4 font-medium">สถานที่</th>
                                <th class="py-3 px-4 font-medium w-1/4">รายละเอียดปัญหา</th>
                                <th class="py-3 px-4 font-medium text-center">รูปภาพ</th>
                                <th class="py-3 px-4 font-medium text-center">สถานะ</th>
                                <th class="py-3 px-4 font-medium text-center">อัปเดตสถานะ</th>
                            </tr>
                        </thead>
                        <tbody class="text-sm text-gray-700 divide-y divide-gray-100">
                            
                            <?php if ($result && $result->num_rows > 0): ?>
                                <?php while($row = $result->fetch_assoc()): ?>
                                    <tr class="hover:bg-gray-50 transition">
                                        
                                        <td class="py-4 px-4 align-top">
                                            <div class="font-semibold text-gray-900"><?php echo htmlspecialchars($row['student_id']); ?></div>
                                            <div class="text-xs text-gray-500 mt-1"><?php echo date('d/m/Y H:i', strtotime($row['created_at'])); ?></div>
                                        </td>
                                        
                                        <td class="py-4 px-4 align-top">
                                            <div><span class="font-medium text-gray-800">ตึก:</span> <?php echo htmlspecialchars($row['building']); ?></div>
                                            <div class="mt-1"><span class="font-medium text-gray-800">ชั้น:</span> <?php echo htmlspecialchars($row['floor']); ?></div>
                                        </td>
                                        
                                        <td class="py-4 px-4 align-top text-gray-600">
                                            <?php echo nl2br(htmlspecialchars($row['description'])); ?>
                                        </td>
                                        
                                        <td class="py-4 px-4 align-top text-center">
                                            <?php if (!empty($row['image_path'])): ?>
                                                <a href="<?php echo htmlspecialchars($row['image_path']); ?>" target="_blank" class="inline-block relative group">
                                                    <img src="<?php echo htmlspecialchars($row['image_path']); ?>" alt="รูปปัญหา" class="w-16 h-16 object-cover rounded-md border border-gray-200 shadow-sm">
                                                    <div class="absolute inset-0 bg-black bg-opacity-50 flex items-center justify-center opacity-0 group-hover:opacity-100 transition rounded-md">
                                                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                                                    </div>
                                                </a>
                                            <?php else: ?>
                                                <span class="text-gray-400 text-xs">- ไม่มีรูป -</span>
                                            <?php endif; ?>
                                        </td>
                                        
                                        <td class="py-4 px-4 align-top text-center">
                                            <?php
                                                $status = $row['status'];
                                                $badge_class = 'bg-gray-100 text-gray-800'; 
                                                
                                                if ($status === 'รอดำเนินการ') {
                                                    $badge_class = 'bg-yellow-100 text-yellow-800 border border-yellow-200';
                                                } elseif ($status === 'กำลังดำเนินการ') {
                                                    $badge_class = 'bg-blue-100 text-blue-800 border border-blue-200';
                                                } elseif ($status === 'เสร็จสิ้น') {
                                                    $badge_class = 'bg-green-100 text-green-800 border border-green-200';
                                                } elseif ($status === 'ยกเลิก') {
                                                    $badge_class = 'bg-red-100 text-red-800 border border-red-200';
                                                }
                                            ?>
                                            <span class="inline-block px-3 py-1 rounded-full text-xs font-medium <?php echo $badge_class; ?>">
                                                <?php echo htmlspecialchars($status); ?>
                                            </span>
                                        </td>
                                        
                                        <td class="py-4 px-4 align-top">
                                            <form method="POST" action="" class="flex flex-col gap-2 items-center">
                                                <input type="hidden" name="action" value="update_status">
                                                <input type="hidden" name="ticket_id" value="<?php echo $row['id']; ?>">
                                                <input type="hidden" name="search_student_id" value="<?php echo htmlspecialchars($search_student_id); ?>">
                                                
                                                <select name="status" class="w-full text-sm border border-gray-300 rounded-md px-2 py-1.5 focus:outline-none focus:border-[#F0441C] focus:ring-1 focus:ring-[#F0441C] bg-white transition">
                                                    <option value="รอดำเนินการ" <?php echo ($status === 'รอดำเนินการ') ? 'selected' : ''; ?>>รอดำเนินการ</option>
                                                    <option value="กำลังดำเนินการ" <?php echo ($status === 'กำลังดำเนินการ') ? 'selected' : ''; ?>>กำลังดำเนินการ</option>
                                                    <option value="เสร็จสิ้น" <?php echo ($status === 'เสร็จสิ้น') ? 'selected' : ''; ?>>เสร็จสิ้น</option>
                                                    <option value="ยกเลิก" <?php echo ($status === 'ยกเลิก') ? 'selected' : ''; ?>>ยกเลิก</option>
                                                </select>
                                                
                                                <!-- เปลี่ยนปุ่มอัปเดตสถานะเป็นโทนสีหลัก -->
                                                <button type="submit" class="w-full bg-[#F0441C] hover:bg-[#D93A16] text-white text-xs py-2 rounded-md transition shadow-sm font-medium">
                                                    บันทึกสถานะ
                                                </button>
                                            </form>
                                        </td>
                                        
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="py-12 text-center">
                                        <div class="text-gray-300 mb-3">
                                            <svg class="mx-auto h-12 w-12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                        </div>
                                        <p class="text-gray-600 font-medium text-lg">ไม่พบประวัติการแจ้งซ่อม</p>
                                        <p class="text-sm text-gray-400 mt-1">ไม่มีข้อมูลตั๋วแจ้งซ่อมสำหรับรหัสนักศึกษา "<?php echo htmlspecialchars($search_student_id); ?>"</p>
                                    </td>
                                </tr>
                            <?php endif; ?>

                        </tbody>
                    </table>
                </div>
            </div>
        <?php endif; ?>

    </div>
</body>
</html>
<?php
if (isset($conn)) {
    $conn->close();
}
?>