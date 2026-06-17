<?php
session_start();
include('../connection/connect.php');
error_reporting(0);
hawkergo_hawker_logout();
header('Location: index.php');
exit;
