<?php
/**
 * API for Laser Labyrinth – same behaviour as Node server.
 * Use with Deploy Now PHP project; set DB_* in .env / GitHub secrets.
 */
$envFile = dirname(__DIR__) . '/.env';
if (is_file($envFile)) {
    foreach (file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [] as $line) {
        $line = trim($line);
        if ($line !== '' && strpos($line, '#') !== 0 && strpos($line, '=') !== false) {
            list($k, $v) = explode('=', $line, 2);
            putenv(trim($k) . '=' . trim(trim($v), '"\''));
        }
    }
}
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

$path = isset($_GET['path']) ? trim($_GET['path'], '/') : '';
$method = $_SERVER['REQUEST_METHOD'];

function db(): PDO {
    static $pdo = null;
    if ($pdo !== null) return $pdo;
    $host = getenv('DB_HOST') ?: 'db5019938968.hosting-data.io';
    $port = getenv('DB_PORT') ?: '3306';
    $user = getenv('DB_USER') ?: 'dbu2298835';
    $pass = getenv('DB_PASSWORD');
    $name = getenv('DB_NAME') ?: 'dbs15396335';
    if ($pass === false || $pass === '') {
        throw new RuntimeException('DB_PASSWORD is required');
    }
    $dsn = "mysql:host=$host;port=$port;dbname=$name;charset=utf8mb4";
    $pdo = new PDO($dsn, $user, $pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    ensureTables($pdo);
    return $pdo;
}

function ensureTables(PDO $pdo): void {
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS progress (
            id INT PRIMARY KEY DEFAULT 1,
            completed_levels JSON,
            current_level INT DEFAULT 0,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            CONSTRAINT single_row CHECK (id = 1)
        )
    ");
    $pdo->exec("INSERT IGNORE INTO progress (id, completed_levels, current_level) VALUES (1, '[]', 0)");
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS custom_levels (
            id VARCHAR(64) PRIMARY KEY,
            name VARCHAR(255) NOT NULL,
            data JSON NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )
    ");
}

function jsonResponse($data): void {
    echo json_encode($data);
}

function jsonError(string $msg, int $code = 500): void {
    http_response_code($code);
    jsonResponse(['error' => $msg]);
}

try {
    if ($path === 'progress') {
        if ($method === 'GET') {
            $stmt = db()->query('SELECT completed_levels, current_level FROM progress WHERE id = 1');
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$row) {
                jsonResponse(['completedLevels' => [], 'currentLevel' => 0]);
            } else {
                $cl = $row['completed_levels'];
                if (is_string($cl)) $cl = json_decode($cl, true) ?: [];
                jsonResponse([
                    'completedLevels' => $cl,
                    'currentLevel' => (int) $row['current_level'],
                ]);
            }
            exit;
        }
        if ($method === 'POST') {
            $input = json_decode(file_get_contents('php://input'), true) ?: [];
            $completedLevels = $input['completedLevels'] ?? [];
            $currentLevel = (int) ($input['currentLevel'] ?? 0);
            $stmt = db()->prepare('UPDATE progress SET completed_levels = ?, current_level = ? WHERE id = 1');
            $stmt->execute([json_encode($completedLevels), $currentLevel]);
            jsonResponse(['ok' => true]);
            exit;
        }
    }

    if ($path === 'custom-levels') {
        if ($method === 'GET') {
            $stmt = db()->query('SELECT id, name, data, updated_at FROM custom_levels ORDER BY updated_at DESC');
            $list = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $raw = is_string($row['data']) ? json_decode($row['data'], true) : $row['data'];
                $list[] = [
                    'id' => $row['id'],
                    'name' => $row['name'],
                    'objects' => $raw['objects'] ?? [],
                    'updated_at' => $row['updated_at'],
                ];
            }
            jsonResponse($list);
            exit;
        }
        if ($method === 'POST') {
            $input = json_decode(file_get_contents('php://input'), true) ?: [];
            $id = $input['id'] ?? '';
            $name = $input['name'] ?? '';
            $objects = $input['objects'] ?? [];
            if ($id === '' || $name === '' || !is_array($objects)) {
                jsonError('id, name, and objects required', 400);
                exit;
            }
            $data = json_encode(['objects' => $objects]);
            $stmt = db()->prepare('INSERT INTO custom_levels (id, name, data) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE name = ?, data = ?');
            $stmt->execute([$id, $name, $data, $name, $data]);
            jsonResponse(['ok' => true, 'id' => $id]);
            exit;
        }
    }

    if (preg_match('#^custom-levels/(.+)$#', $path, $m) && $method === 'DELETE') {
        $id = $m[1];
        $stmt = db()->prepare('DELETE FROM custom_levels WHERE id = ?');
        $stmt->execute([$id]);
        jsonResponse(['ok' => true]);
        exit;
    }

    if ($path === 'health') {
        db();
        jsonResponse(['ok' => true, 'db' => 'connected']);
        exit;
    }

    jsonError('Not found', 404);
} catch (Throwable $e) {
    error_log($e->getMessage());
    jsonError('Database error', 500);
}
