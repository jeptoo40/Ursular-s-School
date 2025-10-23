
<?php
require_once 'config.php';

if (isset($_POST['id'])) {
    $id = $_POST['id'];
    $subject_name = trim($_POST['subject_name']);
    $subject_code = trim($_POST['subject_code']);
    $description = trim($_POST['description']);

    if (!empty($subject_name) && !empty($subject_code)) {
        $stmt = $conn->prepare("UPDATE subjects SET subject_name=?, subject_code=?, description=? WHERE id=?");
        $stmt->bind_param("sssi", $subject_name, $subject_code, $description, $id);
        
        if ($stmt->execute()) {
            echo "✅ Subject updated successfully!";
        } else {
            echo "❌ Error updating subject: " . $conn->error;
        }
    } else {
        echo "⚠️ Please fill all required fields.";
    }
} else {
    echo "Invalid request.";
}
?>
