<?php
// เพิ่ม ob_start() เพื่อป้องกันปัญหาการส่ง Header ซ้ำซ้อน
ob_start();
session_start();

$error_message = ''; // ตัวแปรสำหรับเก็บข้อความแจ้งเตือน

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "database_fcn";

    error_reporting(E_ALL);
    ini_set('display_errors', 1);

    try {
        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

        $conn = new mysqli($servername, $username, $password, $dbname);
        $conn->set_charset("utf8mb4");

        $student_id_input = trim($_POST['username'] ?? '');
        $pass = $_POST['password'] ?? '';

        if (empty($student_id_input) || empty($pass)) {
            $error_message = 'กรุณากรอกรหัสนักศึกษาและรหัสผ่านให้ครบถ้วน';
        } else {
            $stmt = $conn->prepare("SELECT student_id, name, lastname, role, password FROM users WHERE student_id = ?");

            $stmt->bind_param("s", $student_id_input);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($row = $result->fetch_assoc()) {
                // ตรวจสอบรหัสผ่าน
                if (password_verify($pass, $row['password'])) {

                    $_SESSION['user_id'] = $row['student_id'];
                    $_SESSION['name'] = $row['name'];
                    $_SESSION['lastname'] = $row['lastname'];
                    $_SESSION['role'] = $row['role'];

                    $stmt->close();
                    $conn->close();

                    // ใช้ JavaScript บังคับเปลี่ยนหน้าแทน PHP Header เพื่อแก้ปัญหาหน้าขาว/ไม่ยอมเปลี่ยนหน้า
                    echo "<script>window.location.href = 'index.php';</script>";
                    exit();
                } else {
                    $error_message = 'รหัสผ่านไม่ถูกต้อง';
                }
            } else {
                $error_message = 'ไม่พบข้อมูลผู้ใช้นี้ในระบบ';
            }
            $stmt->close();
        }
        $conn->close();
    } catch (mysqli_sql_exception $e) {
        $error_message = 'Database Error: ' . $e->getMessage();
    } catch (Throwable $e) {
        // ดักจับ Error ร้ายแรงทั้งหมด (เปลี่ยนจาก Exception เป็น Throwable)
        $error_message = 'System Error: ' . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>เข้าสู่ระบบ</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Prompt', sans-serif;
        }
    </style>
</head>

<body class="bg-gray-50 flex items-center justify-center min-h-screen py-10">
    <div class="w-full max-w-[460px] px-4">

        <div class="flex items-center justify-between mb-3 px-1">
            <h1 class="text-xl font-semibold text-gray-800">เข้าสู่ระบบ</h1>
            <span class="text-[#F0441C] font-medium text-lg">Signin</span>
        </div>

        <div class="bg-white border border-gray-200 rounded-lg p-6 md:p-8 shadow-sm">

            <?php if (!empty($error_message)): ?>
                <div class="mb-4 bg-red-50 border-l-4 border-red-500 text-red-700 p-3 text-sm rounded-md shadow-sm">
                    <?php echo htmlspecialchars($error_message); ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="">

                <div class="mb-5">
                    <label for="username" class="block text-gray-800 text-sm font-medium mb-2">รหัสนักศึกษา / Student ID</label>
                    <input type="text" name="username" id="username" placeholder="รหัสนักศึกษา"
                        value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>"
                        class="w-full px-4 py-2.5 text-gray-700 border border-gray-200 rounded-md focus:outline-none focus:border-[#F0441C] focus:ring-1 focus:ring-[#F0441C] transition" required>
                </div>

                <div class="mb-4">
                    <label for="password" class="block text-gray-800 text-sm font-medium mb-2">รหัสผ่าน / Password</label>
                    <input type="password" name="password" id="password" placeholder="**********"
                        class="w-full px-4 py-2.5 text-gray-700 border border-gray-200 rounded-md focus:outline-none focus:border-[#F0441C] focus:ring-1 focus:ring-[#F0441C] transition" required>
                </div>

                <div class="mb-5 text-left">
                    <a href="#" class="text-sm text-[#F0441C] hover:underline">ลืมรหัสผ่าน</a>
                </div>

                <button type="submit" class="w-full bg-[#F0441C] hover:bg-[#D93A16] text-white font-medium py-2.5 px-4 rounded-md transition duration-200">
                    เข้าสู่ระบบ
                </button>

            </form>
        </div>

        <div class="mt-6 text-center text-gray-500 text-sm">
            ยังไม่ได้สร้างบัญชีใช่ไหม? <a href="register.php" class="text-[#F0441C] hover:underline">คลิ๊กที่นี่เพื่อไปหน้าทำการสร้างบัญชี</a>
        </div>

    </div>
</body>

</html>