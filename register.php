<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "database_fcn";

// ปิด Error เพื่อจัดการเอง
error_reporting(0);
$error_message = '';
$success_message = '';

$conn = @new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    $error_message = 'เชื่อมต่อ DB ไม่ได้: ' . $conn->connect_error;
} else {
    $conn->set_charset("utf8mb4");

    // ตรวจสอบว่ามีการกด Submit ฟอร์มมาหรือไม่
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $student_id = trim($_POST['student_id'] ?? '');
        $name = trim($_POST['name'] ?? '');
        $lastname = trim($_POST['lastname'] ?? '');
        $department = $_POST['department'] ?? '';
        $pass = $_POST['password'] ?? '';
        $repeat_password = $_POST['repeat_password'] ?? '';

        // ตรวจสอบข้อมูลว่ากรอกครบไหม
        if (empty($student_id) || empty($name) || empty($lastname) || empty($department) || empty($pass) || empty($repeat_password)) {
             $error_message = 'กรุณากรอกข้อมูลให้ครบทุกช่อง';
        } 
        // ตรวจสอบรหัสผ่านว่าตรงกันหรือไม่
        elseif ($pass !== $repeat_password) {
             $error_message = 'รหัสผ่านและการยืนยันรหัสผ่านไม่ตรงกัน!';
        } else {
            // เข้ารหัสผ่าน
            $hashed_password = password_hash($pass, PASSWORD_DEFAULT);

            // บันทึกลงฐานข้อมูล
            $stmt = $conn->prepare("INSERT INTO users (student_id, name, lastname, department, password) VALUES (?, ?, ?, ?, ?)");
            
            if ($stmt) {
                $stmt->bind_param("sssss", $student_id, $name, $lastname, $department, $hashed_password);

                if ($stmt->execute()) {
                    // แจ้งเตือนเมื่อสำเร็จ
                    $success_message = 'สมัครสมาชิกสำเร็จ';
                    // รีเซ็ตค่าเพื่อไม่ให้ค้างในฟอร์ม (ลบข้อมูลออกจาก $_POST)
                    $_POST = array(); 
                } else {
                    $error_message = 'เกิดข้อผิดพลาด: รหัสนักศึกษานี้อาจมีในระบบแล้ว';
                }
                $stmt->close();
            } else {
                 $error_message = 'SQL Error';
            }
        }
    }
}
?>
<!doctype html>
<html lang="th">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>สมัครสมาชิก (นักศึกษา)</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;500;600&display=swap" rel="stylesheet" />
  <style>
    body {
      font-family: "Prompt", sans-serif;
    }
  </style>
</head>

<body class="bg-gray-50 flex items-center justify-center min-h-screen py-10">
  <div class="w-full max-w-[500px] px-4">
    <div class="flex items-center justify-between mb-3 px-1">
      <h1 class="text-xl font-semibold text-gray-800">ลงทะเบียนนักศึกษา</h1>
      <span class="text-[#F0441C] font-medium text-lg">Register</span>
    </div>
    
    <div class="bg-white border border-gray-200 rounded-lg p-6 md:p-8 shadow-sm">
      
      <!-- ส่วนแสดงข้อความแจ้งเตือน Error (สีแดง) -->
      <?php if (!empty($error_message)): ?>
          <div class="mb-4 bg-red-50 border-l-4 border-red-500 text-red-700 p-3 text-sm rounded-md shadow-sm">
              <?php echo htmlspecialchars($error_message); ?>
          </div>
      <?php endif; ?>

      <!-- ส่วนแสดงข้อความแจ้งเตือน Success (สีเขียว) -->
      <?php if (!empty($success_message)): ?>
          <div class="mb-4 bg-green-50 border-l-4 border-green-500 text-green-700 p-3 text-sm rounded-md shadow-sm">
              <span class="font-medium text-base"><?php echo htmlspecialchars($success_message); ?></span>
              <br>
              <a href="login.php" class="font-bold hover:underline mt-2 inline-block text-green-800">คลิกที่นี่เพื่อไปหน้าเข้าสู่ระบบ &rarr;</a>
          </div>
      <?php endif; ?>

      <form action="" method="POST">
        <div class="mb-5">
          <label for="student_id" class="block text-gray-800 text-sm font-medium mb-2">รหัสนักศึกษา / Student ID</label>
          <input type="text" id="student_id" name="student_id" placeholder="ตัวอย่าง: 66209010001" 
                 value="<?php echo isset($_POST['student_id']) ? htmlspecialchars($_POST['student_id']) : ''; ?>"
                 class="w-full px-4 py-2.5 text-gray-700 border border-gray-200 rounded-md focus:outline-none focus:border-[#F0441C] focus:ring-1 focus:ring-[#F0441C] transition bg-white" required />
          <p class="text-gray-400 text-sm mt-1.5">ใช้เป็นรหัสในการเข้าสู่ระบบ</p>
        </div>
        <div class="grid grid-cols-2 gap-4 mb-5">
          <div>
            <label for="name" class="block text-gray-800 text-sm font-medium mb-2">ชื่อ / Name</label>
            <input type="text" id="name" name="name" placeholder="ชื่อจริง" 
                   value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>"
                   class="w-full px-4 py-2.5 text-gray-700 border border-gray-200 rounded-md focus:outline-none focus:border-[#F0441C] focus:ring-1 focus:ring-[#F0441C] transition bg-white" required />
          </div>
          <div>
            <label for="lastname" class="block text-gray-800 text-sm font-medium mb-2">นามสกุล / Lastname</label>
            <input type="text" id="lastname" name="lastname" placeholder="นามสกุล" 
                   value="<?php echo isset($_POST['lastname']) ? htmlspecialchars($_POST['lastname']) : ''; ?>"
                   class="w-full px-4 py-2.5 text-gray-700 border border-gray-200 rounded-md focus:outline-none focus:border-[#F0441C] focus:ring-1 focus:ring-[#F0441C] transition bg-white" required />
          </div>
        </div>
        <div class="mb-5">
          <label for="department" class="block text-gray-800 text-sm font-medium mb-2">สาขาวิชา / Department</label>
          <div class="relative">
            <select id="department" name="department" class="appearance-none w-full px-4 py-2.5 pr-10 text-gray-700 border border-gray-200 rounded-md focus:outline-none focus:border-[#F0441C] focus:ring-1 focus:ring-[#F0441C] transition bg-white" required>
              <option value="" disabled <?php echo empty($_POST['department']) ? 'selected' : ''; ?>>-- เลือกสาขาวิชา --</option>
              <option value="Information Technology" <?php echo (isset($_POST['department']) && $_POST['department'] == 'Information Technology') ? 'selected' : ''; ?>>เทคโนโลยีสารสนเทศ (IT)</option>
              <option value="Computer Science" <?php echo (isset($_POST['department']) && $_POST['department'] == 'Computer Science') ? 'selected' : ''; ?>>วิทยาการคอมพิวเตอร์</option>
              <option value="Electronics" <?php echo (isset($_POST['department']) && $_POST['department'] == 'Electronics') ? 'selected' : ''; ?>>อิเล็กทรอนิกส์</option>
              <option value="Other" <?php echo (isset($_POST['department']) && $_POST['department'] == 'Other') ? 'selected' : ''; ?>>อื่นๆ</option>
            </select>
            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4 text-gray-500">
              <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                <path d="M9.293 12.95l.707.707L15.657 8l-1.414-1.414L10 10.828 5.757 6.586 4.343 8z" />
              </svg>
            </div>
          </div>
        </div>
        <div class="mb-5">
          <label for="password" class="block text-gray-800 text-sm font-medium mb-2">รหัสผ่าน / Password</label>
          <input type="password" id="password" name="password" placeholder="**********" class="w-full px-4 py-2.5 text-gray-700 border border-gray-200 rounded-md focus:outline-none focus:border-[#F0441C] focus:ring-1 focus:ring-[#F0441C] transition bg-white" required />
        </div>
        <div class="mb-6">
          <label for="repeat_password" class="block text-gray-800 text-sm font-medium mb-2">ยืนยันรหัสผ่าน / Repeat Password</label>
          <input type="password" id="repeat_password" name="repeat_password" placeholder="**********" class="w-full px-4 py-2.5 text-gray-700 border border-gray-200 rounded-md focus:outline-none focus:border-[#F0441C] focus:ring-1 focus:ring-[#F0441C] transition bg-white" required />
          <p class="text-gray-400 text-sm mt-1.5">เพื่อยืนยันว่าคุณไม่ได้กรอกรหัสผ่านผิด</p>
        </div>
        
        <!-- ปุ่ม Submit แสดงตลอดเวลา -->
        <button type="submit" class="w-full bg-[#F0441C] hover:bg-[#D93A16] text-white font-medium py-2.5 px-4 rounded-md transition duration-200">สมัครสมาชิก</button>
        
      </form>
    </div>
    <div class="mt-6 text-center text-gray-500 text-sm">
      มีบัญชีอยู่แล้วใช่ไหม? <a href="login.php" class="text-[#F0441C] hover:underline">คลิ๊กที่นี่เพื่อไปหน้าเข้าสู่ระบบ</a>
    </div>
  </div>
</body>
</html>