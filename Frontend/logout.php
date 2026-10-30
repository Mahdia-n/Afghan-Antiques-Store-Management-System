<?php
session_start();

// Clear all session data
$_SESSION = [];

// Destroy the session
session_destroy();

// Return the user to the home page
header('Location: index.php');
exit;
