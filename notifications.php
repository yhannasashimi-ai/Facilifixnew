<?php
require 'auth.php';
require_role('Admin');
include 'partials.php';

$result = $conn->query(
    "SELECT r.id, r.status_reported, r.remarks, r.created_at, r.is_read,
            u.name AS reporter, e.equipment_name, rm.id AS room_id, rm.room_name
     FROM reports r
     JOIN users u     ON u.id = r.user_id AND u.role = 'Staff'
     JOIN equipment e ON e.id = r.equipment_id
     JOIN rooms rm    ON rm.id = e.room_id
     ORDER BY r.created_at DESC, r.id DESC
     LIMIT 100"
);
$rows = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];

// Mark as read AFTER fetching, so this visit still shows which ones were new
$conn->query("UPDATE reports r JOIN users u ON u.id = r.user_id
              SET r.is_read = 1 WHERE r.is_read = 0 AND u.role = 'Staff'");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifications - FaciliFix</title>
    <link href="css/base.css" rel="stylesheet">
    <link href="css/app.css" rel="stylesheet">
</head>
<body>

    <?php render_topbar('notifications'); ?>

    <main class="page">

        <div class="page-head">
            <?php render_back('admin_dashboard.php', 'Back to dashboard'); ?>
            <h1>Notifications</h1>
        </div>
        <hr class="page-divider">

        <div class="table-wrap">
            <table class="equipment-table">
                <thead>
                    <tr>
                        <th>Room</th>
                        <th>Equipment</th>
                        <th>Status</th>
                        <th class="col-desc">Comments</th>
                        <th>Reported By</th>
                        <th>When</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($rows as $n): ?>
                        <tr class="<?php echo $n['is_read'] ? '' : 'unread'; ?>">
                            <td><?php echo htmlspecialchars($n['room_name']); ?></td>
                            <td class="eq-name"><?php echo htmlspecialchars($n['equipment_name']); ?></td>
                            <td><span class="status status-<?php echo htmlspecialchars(strtolower($n['status_reported'])); ?>"><?php echo htmlspecialchars($n['status_reported']); ?></span></td>
                            <td class="col-desc">
                                <?php if (trim((string)$n['remarks']) !== ''): ?>
                                    <span class="desc"><?php echo nl2br(htmlspecialchars($n['remarks'])); ?></span>
                                <?php else: ?>
                                    <span class="desc-empty">No comments</span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo htmlspecialchars($n['reporter']); ?></td>
                            <td><?php echo date('d/m/Y h:i A', strtotime($n['created_at'])); ?></td>
                            <td>
                                <div class="row-actions">
                                    <a href="room.php?id=<?php echo (int)$n['room_id']; ?>" class="btn btn-gray btn-block">View Room</a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <?php if (count($rows) === 0): ?>
                <p class="empty-message">No staff reports yet.</p>
            <?php endif; ?>
        </div>

    </main>

    <script src="js/layout.js"></script>
</body>
</html>