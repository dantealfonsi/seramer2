<?php
require_once __DIR__ . '/AuditLogModel.php';

class Audit {
    public static function logInsert($table, $id, $data) {
        $model = new AuditLogModel();
        // Determine user ID from session if available
        $userId = $_SESSION['user_id'] ?? null;
        $model->log($userId, 'INSERT', $table, $id, null, json_encode($data));
    }

    public static function logUpdate($table, $id, $oldData, $newData) {
        $model = new AuditLogModel();
        $userId = $_SESSION['user_id'] ?? null;
        $model->log($userId, 'UPDATE', $table, $id, json_encode($oldData), json_encode($newData));
    }

    public static function logDelete($table, $id, $oldData) {
        $model = new AuditLogModel();
        $userId = $_SESSION['user_id'] ?? null;
        $model->log($userId, 'DELETE', $table, $id, json_encode($oldData), null);
    }
}
