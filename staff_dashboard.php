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

// ดึงรหัสนักศึกษาทั้งหมดเพื่อนำมาสร้างเป็นตัวเลือกใน Dropdown
$all_students = [];
$sql_students = "SELECT DISTINCT student_id FROM repair_tickets ORDER BY student_id ASC";
$result_students = $conn->query($sql_students);
if ($result_students && $result_students->num_rows > 0) {
    while($row_student = $result_students->fetch_assoc()) {
        $raw_id = str_replace('ticket_', '', $row_student['student_id']);
        $all_students[] = $raw_id;
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
        
        /* ซ่อน Scrollbar เริ่มต้น แต่ยังเลื่อนได้ */
        .custom-scrollbar::-webkit-scrollbar {
            width: 6px;
        }
        .custom-scrollbar::-webkit-scrollbar-track {
            background: #f1f1f1; 
            border-radius: 4px;
        }
        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: #cbd5e1; 
            border-radius: 4px;
        }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover {
            background: #94a3b8; 
        }
    </style>
</head>
<body class="bg-gray-50 min-h-screen">

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
        
        <!-- กล่องค้นหาแบบ Custom Dropdown -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-8 max-w-xl mx-auto">
            <h2 class="text-lg font-semibold text-gray-800 mb-4 text-center">ค้นหารายการแจ้งซ่อมของนักศึกษา</h2>
            
            <form method="GET" action="" id="search-form" class="flex flex-col sm:flex-row gap-3">
                <div class="relative w-full" id="dropdown-container">
                    
                    <!-- ช่องพิมพ์ค้นหา (อัปเกรดหน้าตา) -->
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </div>
                        <input type="text" id="search-input" name="search" value="<?php echo htmlspecialchars($search_student_id); ?>" 
                               placeholder="ค้นหาหรือเลือกรหัสนักศึกษา..." 
                               class="w-full pl-10 pr-10 py-3 text-gray-700 border border-gray-300 rounded-md focus:outline-none focus:border-[#F0441C] focus:ring-1 focus:ring-[#F0441C] transition bg-white" 
                               autocomplete="off">
                        <!-- ไอคอนลูกศรลง -->
                        <div class="absolute inset-y-0 right-0 pr-3 flex items-center cursor-pointer" id="dropdown-toggle">
                            <svg class="h-5 w-5 text-gray-400 hover:text-gray-600 transition" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </div>
                    </div>

                    <!-- กล่อง Dropdown Options (ซ่อนไว้ก่อน) -->
                    <div id="dropdown-menu" class="absolute z-10 mt-1 w-full bg-white rounded-md shadow-lg border border-gray-200 hidden">
                        <ul class="max-h-60 overflow-y-auto custom-scrollbar py-1 text-sm text-gray-700" id="options-list">
                            <?php if (empty($all_students)): ?>
                                <li class="px-4 py-3 text-gray-500 text-center">ไม่มีข้อมูลรหัสนักศึกษาในระบบ</li>
                            <?php else: ?>
                                <?php foreach($all_students as $std_id): ?>
                                    <li class="dropdown-option cursor-pointer select-none px-4 py-2.5 hover:bg-orange-50 hover:text-[#F0441C] transition flex items-center" data-value="<?php echo htmlspecialchars($std_id); ?>">
                                        <svg class="h-4 w-4 mr-2 text-gray-400 option-icon hidden" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                                        <?php echo htmlspecialchars($std_id); ?>
                                    </li>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </ul>
                        <!-- กรณีค้นหาแล้วไม่เจอ -->
                        <div id="no-results" class="hidden px-4 py-3 text-sm text-gray-500 text-center">
                            ไม่พบรหัสที่ค้นหา
                        </div>
                    </div>
                    
                </div>

                <button type="submit" class="bg-[#F0441C] hover:bg-[#D93A16] text-white font-medium px-6 py-3 rounded-md transition whitespace-nowrap shadow-sm h-[50px]">
                    ค้นหาข้อมูล
                </button>
            </form>
        </div>

        <!-- ส่วนของตารางแสดงผล (เหมือนเดิม) -->
        <?php if (isset($_GET['search'])): ?>
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                <div class="p-6 border-b border-gray-200 bg-gray-50 flex justify-between items-center">
                    <div>
                        <h2 class="text-lg font-semibold text-gray-800">ผลการค้นหา</h2>
                        <p class="text-sm text-gray-500">รหัสนักศึกษา: <span class="font-semibold text-gray-700"><?php echo htmlspecialchars($search_student_id); ?></span></p>
                    </div>
                    <?php if ($result && $result->num_rows > 0): ?>
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

    <!-- Script ควบคุม Custom Dropdown -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('search-input');
            const dropdownToggle = document.getElementById('dropdown-toggle');
            const dropdownMenu = document.getElementById('dropdown-menu');
            const optionsList = document.getElementById('options-list');
            const options = document.querySelectorAll('.dropdown-option');
            const noResults = document.getElementById('no-results');

            // เปิด/ปิด Dropdown เมื่อคลิกที่ลูกศรหรือช่องพิมพ์
            function toggleDropdown() {
                dropdownMenu.classList.toggle('hidden');
            }

            searchInput.addEventListener('click', () => {
                dropdownMenu.classList.remove('hidden');
            });

            dropdownToggle.addEventListener('click', (e) => {
                e.stopPropagation();
                toggleDropdown();
                searchInput.focus();
            });

            // ปิด Dropdown เมื่อคลิกที่อื่น
            document.addEventListener('click', function(e) {
                if (!document.getElementById('dropdown-container').contains(e.target)) {
                    dropdownMenu.classList.add('hidden');
                }
            });

            // ค้นหาและกรองข้อมูลเมื่อพิมพ์
            searchInput.addEventListener('input', function() {
                const filter = this.value.toLowerCase();
                let hasResults = false;

                dropdownMenu.classList.remove('hidden');

                options.forEach(option => {
                    const text = option.textContent.toLowerCase();
                    if (text.includes(filter)) {
                        option.classList.remove('hidden');
                        hasResults = true;
                    } else {
                        option.classList.add('hidden');
                    }
                });

                if (hasResults) {
                    noResults.classList.add('hidden');
                    optionsList.classList.remove('hidden');
                } else {
                    noResults.classList.remove('hidden');
                    optionsList.classList.add('hidden');
                }
            });

            // เมื่อคลิกเลือกตัวเลือกใน Dropdown
            options.forEach(option => {
                option.addEventListener('click', function() {
                    const val = this.getAttribute('data-value');
                    searchInput.value = val;
                    dropdownMenu.classList.add('hidden');
                    
                    // อัปเดต UI ให้รู้ว่าเลือกตัวนี้อยู่ (โชว์ไอคอนติ๊กถูก)
                    options.forEach(opt => {
                        opt.classList.remove('bg-orange-50', 'text-[#F0441C]');
                        opt.querySelector('.option-icon').classList.add('hidden');
                        opt.querySelector('.option-icon').classList.remove('text-[#F0441C]');
                    });
                    
                    this.classList.add('bg-orange-50', 'text-[#F0441C]');
                    this.querySelector('.option-icon').classList.remove('hidden');
                    this.querySelector('.option-icon').classList.add('text-[#F0441C]');
                });
            });

            // ตรวจสอบค่าเริ่มต้นตอนโหลดหน้าเพื่อแสดงตัวเลือกที่ถูกเลือก
            const currentVal = searchInput.value;
            if (currentVal) {
                options.forEach(option => {
                    if (option.getAttribute('data-value') === currentVal) {
                        option.classList.add('bg-orange-50', 'text-[#F0441C]');
                        option.querySelector('.option-icon').classList.remove('hidden');
                        option.querySelector('.option-icon').classList.add('text-[#F0441C]');
                    }
                });
            }
        });
    </script>
</body>
</html>