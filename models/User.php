<?php
// models/User.php
class User {
    private Database $db;

    public function __construct(Database $db) {
        $this->db = $db;
    }

    public function all(array $filters = [], array $options = []): array {
        return $this->db->select('app_user', '*', $filters, $options);
    }

    public function find(int $id): ?array {
        return $this->db->find('app_user', $id, 'user_id');
    }

    public function findByEmail(string $email): ?array {
        $rows = $this->db->select('app_user', '*', ['email=eq.' . rawurlencode(strtolower(trim($email)))], ['limit' => 1]);
        return $rows[0] ?? null;
    }

    public function create(array $data): array {
        return $this->db->insert('app_user', $data);
    }

    public function update(int $id, array $data): array {
        return $this->db->update('app_user', $data, ['user_id=eq.' . $id]);
    }

    public function delete(int $id): void {
        $this->db->delete('app_user', ['user_id=eq.' . $id]);
    }

    public function emailExists(string $email): bool {
        $email = strtolower(trim($email));
        if ($email === '') {
            return false;
        }
        $rows = $this->db->select('app_user', 'user_id', ['email=eq.' . rawurlencode($email)], ['limit' => 1]);
        return !empty($rows);
    }
}