<?php
session_start();
require_once '../school_admin/config.php'; 

session_unset();
session_destroy();


header("Location: ../index.php"); 
exit;
?>
