<?php
session_start();

// สร้างตัวแปรเช็คสถานะล็อกอิน
$is_logged_in = isset($_SESSION['user_id']);
$default_student_id = $is_logged_in ? $_SESSION['user_id'] : '';

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "database_fcn";

$error_message = '';
$success_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $conn = @new mysqli($servername, $username, $password, $dbname);
    
    if ($conn->connect_error) {
        $error_message = 'เชื่อมต่อฐานข้อมูลไม่ได้: ' . $conn->connect_error;
    } else {
        $conn->set_charset("utf8mb4");

        $raw_student_id = trim($_POST['raw_student_id'] ?? '');
        $building = trim($_POST['building'] ?? '');
        $floor = trim($_POST['floor'] ?? '');
        $description = trim($_POST['description'] ?? '');
        
        if (!preg_match('/^[0-9]+$/', $raw_student_id)) {
             $error_message = 'รหัสอ้างอิงต้องเป็นตัวเลขเท่านั้น';
        }
        elseif (empty($raw_student_id) || empty($building) || empty($floor) || empty($description)) {
            $error_message = 'กรุณากรอกข้อมูลที่จำเป็นให้ครบถ้วน';
        } else {
            $ticket_id = 'ticket_' . $raw_student_id;
            $image_path = null;

            if (isset($_FILES['repair_image']) && $_FILES['repair_image']['error'] === UPLOAD_ERR_OK) {
                $upload_dir = 'uploads/';
                
                if (!is_dir($upload_dir)) {
                    mkdir($upload_dir, 0777, true);
                }

                $file_tmp = $_FILES['repair_image']['tmp_name'];
                $file_name = $_FILES['repair_image']['name'];
                $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
                
                $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

                if (in_array($file_ext, $allowed_extensions)) {
                    
                    // --- ส่วนที่แก้ไข: ระบบตั้งชื่อไฟล์แบบเรียงลำดับ ---
                    $sequence = 1;
                    // ใช้ glob() ตรวจสอบหาไฟล์ที่ชื่อขึ้นต้นด้วย repair_รหัส_ลำดับ ไม่ว่าจะนามสกุลอะไร
                    while (count(glob($upload_dir . 'repair_' . $raw_student_id . '_' . $sequence . '.*')) > 0) {
                        $sequence++; // ถ้ารูปที่ 1 มีแล้ว ให้ขยับไปเช็คเลข 2 เรื่อยๆ
                    }
                    
                    // ประกอบชื่อไฟล์ใหม่ เช่น repair_66209010001_1.jpg
                    $new_file_name = 'repair_' . $raw_student_id . '_' . $sequence . '.' . $file_ext;
                    $destination = $upload_dir . $new_file_name;
                    // ------------------------------------------

                    if (move_uploaded_file($file_tmp, $destination)) {
                        $image_path = $destination;
                    } else {
                        $error_message = 'เกิดข้อผิดพลาดในการบันทึกรูปภาพ';
                    }
                } else {
                    $error_message = 'อนุญาตเฉพาะไฟล์รูปภาพ (JPG, PNG, GIF, WEBP) เท่านั้น';
                }
            }

            if (empty($error_message)) {
                $stmt = $conn->prepare("INSERT INTO repair_tickets (student_id, building, floor, description, image_path) VALUES (?, ?, ?, ?, ?)");
                
                if ($stmt) {
                    $stmt->bind_param("sssss", $ticket_id, $building, $floor, $description, $image_path);
                    
                    if ($stmt->execute()) {
                        $success_message = 'ส่งข้อมูลแจ้งซ่อมเรียบร้อยแล้ว! (รหัสอ้างอิง: ' . htmlspecialchars($ticket_id) . ')';
                        $_POST = array(); 
                    } else {
                        $error_message = 'เกิดข้อผิดพลาดในการบันทึกข้อมูล';
                    }
                    $stmt->close();
                } else {
                    $error_message = 'SQL Error';
                }
            }
        }
        $conn->close();
    }
}
?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ระบบแจ้งซ่อมคอมพิวเตอร์</title>
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

<body class="bg-gray-50 min-h-screen pb-10">

    <nav class="bg-white shadow-sm border-b border-gray-200 px-6 py-4 mb-8">
        <div class="max-w-4xl mx-auto flex flex-wrap justify-between items-center gap-4">
            <h1 class="text-xl font-bold text-gray-800">ระบบแจ้งซ่อม (IT Support)</h1>
            <div class="flex items-center gap-3 md:gap-4">
                
                <?php if ($is_logged_in): ?>
                    <span class="text-sm text-gray-600 hidden md:inline">ผู้ใช้งาน: <span class="font-semibold"><?= htmlspecialchars($_SESSION['name']) ?></span></span>
                    <a href="index.php" class="text-sm text-gray-500 hover:text-gray-800 transition">กลับหน้าแรก</a>
                    <a href="logout.php" class="text-sm bg-gray-100 hover:bg-red-50 hover:text-red-600 text-gray-700 px-3 py-1.5 rounded-md transition border border-gray-200">ออกจากระบบ</a>
                <?php else: ?>
                    <a href="login.php" class="text-sm bg-white border border-gray-300 text-gray-700 hover:bg-gray-50 px-4 py-1.5 rounded-md transition shadow-sm font-medium">เข้าสู่ระบบ</a>
                    <a href="register.php" class="text-sm bg-[#F0441C] text-white hover:bg-[#D93A16] px-4 py-1.5 rounded-md transition shadow-sm font-medium">สมัครสมาชิก</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <div class="max-w-2xl mx-auto px-4">
        
        <div class="bg-white border border-gray-200 rounded-lg shadow-sm p-6 md:p-8">
            <div class="border-b border-gray-100 pb-4 mb-6">
                <h2 class="text-2xl font-semibold text-gray-800">ฟอร์มแจ้งซ่อมคอมพิวเตอร์</h2>
                <p class="text-gray-500 text-sm mt-1">ระบุรายละเอียดและสถานที่เพื่อให้เจ้าหน้าที่เข้าตรวจสอบ</p>
            </div>

            <?php if (!empty($error_message)): ?>
                <div class="mb-6 bg-red-50 border-l-4 border-red-500 text-red-700 p-4 text-sm rounded-md shadow-sm">
                    <?php echo htmlspecialchars($error_message); ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($success_message)): ?>
                <div class="mb-6 bg-green-50 border-l-4 border-green-500 text-green-700 p-4 text-sm rounded-md shadow-sm">
                    <span class="font-medium"><?php echo htmlspecialchars($success_message); ?></span>
                </div>
            <?php endif; ?>

            <form action="" method="POST" enctype="multipart/form-data" class="space-y-5">
                
                <div>
                    <label for="raw_student_id" class="block text-gray-800 text-sm font-medium mb-2">รหัสนักศึกษา (สำหรับใช้อ้างอิง) <span class="text-red-500">*</span></label>
                    <input type="number" id="raw_student_id" name="raw_student_id" 
                           value="<?php echo isset($_POST['raw_student_id']) ? htmlspecialchars($_POST['raw_student_id']) : htmlspecialchars($default_student_id); ?>"
                           class="w-full px-4 py-2.5 text-gray-700 border border-gray-200 rounded-md focus:outline-none focus:border-[#F0441C] focus:ring-1 focus:ring-[#F0441C] transition bg-white" 
                           placeholder="กรอกรหัสนักศึกษาเฉพาะตัวเลข" required>
                    
                    <p class="text-xs text-gray-400 mt-1">
                        <?php if ($is_logged_in): ?>
                            ระบบดึงรหัสของคุณมาให้แล้ว สามารถแก้ไขตัวเลขได้หากต้องการแจ้งแทนผู้อื่น
                        <?php else: ?>
                            กรุณากรอกรหัสนักศึกษาของคุณ ระบบจะใช้รหัสนี้เป็นเลข Ticket ในการอ้างอิงสถานะการซ่อม
                        <?php endif; ?>
                    </p>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="building" class="block text-gray-800 text-sm font-medium mb-2">อาคาร / ตึก <span class="text-red-500">*</span></label>
                        <input type="text" id="building" name="building" placeholder="เช่น อาคาร 3, ตึกอำนวยการ"
                               value="<?php echo isset($_POST['building']) ? htmlspecialchars($_POST['building']) : ''; ?>"
                               class="w-full px-4 py-2.5 text-gray-700 border border-gray-200 rounded-md focus:outline-none focus:border-[#F0441C] focus:ring-1 focus:ring-[#F0441C] transition bg-white" required>
                    </div>
                    <div>
                        <label for="floor" class="block text-gray-800 text-sm font-medium mb-2">ชั้น / ห้อง <span class="text-red-500">*</span></label>
                        <input type="text" id="floor" name="floor" placeholder="เช่น ชั้น 2 ห้อง 324"
                               value="<?php echo isset($_POST['floor']) ? htmlspecialchars($_POST['floor']) : ''; ?>"
                               class="w-full px-4 py-2.5 text-gray-700 border border-gray-200 rounded-md focus:outline-none focus:border-[#F0441C] focus:ring-1 focus:ring-[#F0441C] transition bg-white" required>
                    </div>
                </div>

                <div>
                    <label for="description" class="block text-gray-800 text-sm font-medium mb-2">รายละเอียดปัญหา <span class="text-red-500">*</span></label>
                    <textarea id="description" name="description" rows="4" placeholder="ระบุอาการเสีย เช่น เปิดไม่ติด, จอฟฟ้า, อินเทอร์เน็ตใช้งานไม่ได้"
                              class="w-full px-4 py-2.5 text-gray-700 border border-gray-200 rounded-md focus:outline-none focus:border-[#F0441C] focus:ring-1 focus:ring-[#F0441C] transition bg-white resize-none" required><?php echo isset($_POST['description']) ? htmlspecialchars($_POST['description']) : ''; ?></textarea>
                </div>

                <div>
                    <label class="block text-gray-800 text-sm font-medium mb-2">แนบรูปภาพ (ถ้ามี)</label>
                    <label id="dropzone" for="repair_image" class="mt-1 flex flex-col items-center justify-center w-full h-56 border-2 border-gray-300 border-dashed rounded-md bg-gray-50 hover:bg-gray-100 transition relative cursor-pointer overflow-hidden">
                        
                        <div id="upload-prompt" class="space-y-2 text-center pointer-events-none">
                            <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                                <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                            <div class="flex text-sm text-gray-600 justify-center">
                                <span class="font-medium text-[#F0441C]">คลิกเพื่ออัปโหลด</span>
                                <span class="ml-1">หรือลากไฟล์มาวางที่นี่</span>
                            </div>
                            <p class="text-xs text-gray-500">PNG, JPG, WEBP ขนาดไม่เกิน 5MB</p>
                        </div>

                        <img id="image-preview" src="#" alt="ภาพตัวอย่าง" class="hidden absolute inset-0 w-full h-full object-contain bg-gray-900" />
                        
                        <input id="repair_image" name="repair_image" type="file" class="sr-only" accept="image/png, image/jpeg, image/webp, image/gif">
                    </label>

                    <div id="file-info" class="mt-3 hidden flex justify-between items-center bg-gray-100 px-3 py-2 rounded-md">
                        <p id="file-name" class="text-sm font-medium text-gray-700 truncate mr-4"></p>
                        <button type="button" id="remove-image" class="text-sm font-semibold text-red-500 hover:text-red-700 whitespace-nowrap">ลบรูปภาพ</button>
                    </div>
                </div>

                <button type="submit" class="w-full mt-6 bg-[#F0441C] hover:bg-[#D93A16] text-white font-medium py-3 px-4 rounded-md transition duration-200 shadow-sm">
                    ส่งข้อมูลแจ้งซ่อม
                </button>
            </form>
        </div>
    </div>

    <script>
        const dropzone = document.getElementById('dropzone');
        const fileInput = document.getElementById('repair_image');
        const uploadPrompt = document.getElementById('upload-prompt');
        const imagePreview = document.getElementById('image-preview');
        const fileInfo = document.getElementById('file-info');
        const fileNameDisplay = document.getElementById('file-name');
        const removeImageBtn = document.getElementById('remove-image');

        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
            dropzone.addEventListener(eventName, preventDefaults, false);
        });

        function preventDefaults(e) {
            e.preventDefault();
            e.stopPropagation();
        }

        ['dragenter', 'dragover'].forEach(eventName => {
            dropzone.addEventListener(eventName, highlight, false);
        });

        ['dragleave', 'drop'].forEach(eventName => {
            dropzone.addEventListener(eventName, unhighlight, false);
        });

        function highlight(e) {
            dropzone.classList.add('border-[#F0441C]', 'bg-red-50');
            dropzone.classList.remove('border-gray-300', 'bg-gray-50');
        }

        function unhighlight(e) {
            dropzone.classList.remove('border-[#F0441C]', 'bg-red-50');
            dropzone.classList.add('border-gray-300', 'bg-gray-50');
        }

        dropzone.addEventListener('drop', handleDrop, false);

        function handleDrop(e) {
            const dt = e.dataTransfer;
            const files = dt.files;

            if (files.length > 0) {
                fileInput.files = files; 
                handleFiles(files[0]);
            }
        }

        fileInput.addEventListener('change', function(e) {
            if (this.files.length > 0) {
                handleFiles(this.files[0]);
            }
        });

        function handleFiles(file) {
            if (!file.type.startsWith('image/')) {
                alert('กรุณาอัปโหลดไฟล์รูปภาพเท่านั้น');
                fileInput.value = ''; 
                return;
            }

            fileNameDisplay.textContent = 'ไฟล์ที่เลือก: ' + file.name;
            fileInfo.classList.remove('hidden');
            
            const reader = new FileReader();
            reader.readAsDataURL(file);
            
            reader.onloadend = function() {
                imagePreview.src = reader.result;
                imagePreview.classList.remove('hidden');
                uploadPrompt.classList.add('hidden');
            }
        }

        removeImageBtn.addEventListener('click', function() {
            fileInput.value = ''; 
            imagePreview.src = '';
            imagePreview.classList.add('hidden');
            uploadPrompt.classList.remove('hidden');
            fileInfo.classList.add('hidden');
        });
    </script>
</body>
</html>