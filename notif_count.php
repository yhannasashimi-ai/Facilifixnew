<?php
require 'auth.php';
require_role('Admin', false);   // polling must not count as activity
include 'partials.php';

header('Content-Type: application/json');
echo json_encode(['unread' => unread_report_count()]);