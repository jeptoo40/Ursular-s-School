<?php
require_once '../school_admin/config.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $role = $_POST['role'];

    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ? AND role = ?");
    $stmt->bind_param("ss", $email, $role);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();

        if (password_verify($password, $user['password'])) {
            // ✅ Store base session data
            $_SESSION['id'] = $user['id'];
            $_SESSION['fullname'] = $user['full_name'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['role'] = $user['role'];

            // ✅ If parent, get student_id by matching parent fullname
            if ($role === 'parent') {
                $pstmt = $conn->prepare("SELECT student_id FROM parents WHERE fullname = ?");
                $pstmt->bind_param("s", $user['full_name']);
                $pstmt->execute();
                $pres = $pstmt->get_result();

                if ($prow = $pres->fetch_assoc()) {
                    $_SESSION['student_id'] = $prow['student_id']; // child linked to parent
                }

                $pstmt->close();
                header("Location: ../school_parent/dashboard.php");
                exit;
            } elseif ($role === 'teacher') {
                header("Location: ../school_teacher/dashboard.php");
                exit;
            } elseif ($role === 'admin') {
                header("Location: ../school_admin/dashboard.php");
                exit;
            }
        } else {
            echo "<script>alert('Incorrect password.'); window.history.back();</script>";
        }
    } else {
        echo "<script>alert('No account found with that email and role.'); window.history.back();</script>";
    }

    $stmt->close();
    $conn->close();
}
?>
