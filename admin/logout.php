<?php
// logout.php — Redirects to home page (no actual logout, persistent session stays active)
header('Location: ../index.php');
exit;
