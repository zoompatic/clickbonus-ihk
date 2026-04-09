<?php
// tests/TestCase.php
namespace Tests;

use App\Database;

class TestCase {
    protected $db;

    public function __construct() {
        $this->db = Database::getConnection();
    }

    public function setUp() {
        $this->db->beginTransaction();
    }

    public function tearDown() {
        if ($this->db->inTransaction()) {
            $this->db->rollBack();
        }
    }

    protected function assertEquals($expected, $actual, $message = '') {
        if ($expected !== $actual) {
            throw new \Exception($message ?: "Expected " . var_export($expected, true) . " but got " . var_export($actual, true));
        }
    }

    protected function assertTrue($condition, $message = '') {
        if (!$condition) {
            throw new \Exception($message ?: "Expected true but got false");
        }
    }

    protected function assertFalse($condition, $message = '') {
        if ($condition) {
            throw new \Exception($message ?: "Expected false but got true");
        }
    }
}
