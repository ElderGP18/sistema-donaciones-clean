<?php
declare(strict_types=1);

namespace Tests\Integration;

use PHPUnit\Framework\TestCase;

class DatabaseTest extends TestCase
{
    private ?\mysqli $conn = null;

    protected function setUp(): void
    {
        $host = getenv('DB_HOST') ?: '127.0.0.1';
        $user = getenv('DB_USER') ?: 'root';
        $pass = getenv('DB_PASS') ?: 'root';
        $db   = getenv('DB_NAME') ?: 'donatu_db';

        $conn = @new \mysqli($host, $user, $pass, $db);
        if ($conn->connect_error) {
            $this->markTestSkipped('Base de datos no disponible: ' . $conn->connect_error);
            return;
        }
        $conn->set_charset('utf8mb4');
        $this->conn = $conn;
    }

    protected function tearDown(): void
    {
        $this->conn?->close();
    }

    public function testConnectionSucceeds(): void
    {
        $this->assertNotNull($this->conn);
        $this->assertEmpty($this->conn->connect_error);
    }

    public function testAllTablesExist(): void
    {
        $tables = ['usuarios', 'campanas', 'donantes', 'donaciones', 'egresos'];
        foreach ($tables as $table) {
            $res = $this->conn->query("SHOW TABLES LIKE '{$table}'");
            $this->assertSame(1, $res->num_rows, "La tabla '{$table}' debe existir");
        }
    }

    public function testSelectFromCampanas(): void
    {
        $res = $this->conn->query("SELECT COUNT(*) AS total FROM campanas");
        $this->assertNotFalse($res, 'SELECT en campanas debe funcionar');
        $row = $res->fetch_assoc();
        $this->assertArrayHasKey('total', $row);
    }

    public function testAdminUserExists(): void
    {
        $stmt = $this->conn->prepare("SELECT id_usuario FROM usuarios WHERE correo = ?");
        $email = 'admin@donatu.com';
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $stmt->store_result();
        $this->assertGreaterThan(0, $stmt->num_rows, 'El usuario admin por defecto debe existir');
        $stmt->close();
    }

    public function testInsertAndDeleteDonante(): void
    {
        $stmt = $this->conn->prepare(
            "INSERT INTO donantes (nombre, correo, telefono) VALUES (?, ?, ?)"
        );
        $nombre   = '__test_phpunit__';
        $correo   = 'phpunit_' . time() . '@test.local';
        $telefono = '0000-0000';
        $stmt->bind_param('sss', $nombre, $correo, $telefono);
        $this->assertTrue($stmt->execute(), 'INSERT en donantes debe funcionar');
        $newId = $stmt->insert_id;
        $stmt->close();

        $this->assertGreaterThan(0, $newId, 'El ID insertado debe ser > 0');

        $del = $this->conn->prepare("DELETE FROM donantes WHERE id_donante = ?");
        $del->bind_param('i', $newId);
        $this->assertTrue($del->execute(), 'DELETE en donantes debe funcionar');
        $del->close();
    }

    public function testPreparedStatementAgainstInjection(): void
    {
        $malicious = "1 OR 1=1; DROP TABLE usuarios;--";
        $stmt = $this->conn->prepare("SELECT id_donante FROM donantes WHERE nombre = ?");
        $stmt->bind_param('s', $malicious);
        $stmt->execute();
        $stmt->store_result();
        // Should return 0 rows — no injection executed
        $this->assertSame(0, $stmt->num_rows);
        $stmt->close();
    }
}
