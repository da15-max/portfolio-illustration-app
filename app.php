<?php
require 'session.php';
if (!is_logged_in()) {
  header('Location: login.php');
  exit;
}
?>
