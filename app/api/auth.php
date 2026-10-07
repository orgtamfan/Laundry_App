<?php
require_once(__DIR__ . '/../includes/_functions.php');

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

function authRespond(int $status, array $payload): void
{
	http_response_code($status);
	echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
	exit();
}

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
	if (!in_array($_SESSION['login'] ?? null, ['Admin', 'User', 'Karyawan'], true)) {
		authRespond(200, ['authenticated' => false]);
	}

	authRespond(200, [
		'authenticated' => true,
		'user' => [
			'id' => (int) ($_SESSION['user_id'] ?? 0),
			'name' => $_SESSION['user_name'] ?? 'Administrator',
			'username' => $_SESSION['username'] ?? 'admin',
			'role' => $_SESSION['login'],
		],
	]);
}

if ($method === 'POST') {
	if (!$koneksi) {
		authRespond(500, ['error' => 'Could not connect to the laundry database.']);
	}

	$credentials = json_decode(file_get_contents('php://input'), true);
	$username = is_array($credentials) ? trim((string) ($credentials['username'] ?? '')) : '';
	$password = is_array($credentials) ? ($credentials['password'] ?? '') : '';

	if ($username === '' || !is_string($password) || $password === '') {
		authRespond(422, ['error' => 'Enter your username and password.']);
	}

	$statement = mysqli_prepare(
		$koneksi,
		'SELECT id_user, nama, username, password, level FROM master WHERE username = ? LIMIT 1'
	);
	if (!$statement) {
		authRespond(500, ['error' => 'Could not verify your account.']);
	}

	mysqli_stmt_bind_param($statement, 's', $username);
	mysqli_stmt_execute($statement);
	mysqli_stmt_bind_result($statement, $userId, $name, $storedUsername, $storedPassword, $level);
	$found = mysqli_stmt_fetch($statement);
	mysqli_stmt_close($statement);

	if (!$found || (!password_verify($password, $storedPassword) && !hash_equals($storedPassword, $password))) {
		authRespond(401, ['error' => 'Username or password is incorrect.']);
	}

	if (!in_array($level, ['Admin', 'User', 'Karyawan'], true)) {
		authRespond(403, ['error' => 'This account does not have access to the application.']);
	}

	if (!password_verify($password, $storedPassword)) {
		$hashedPassword = password_hash($password, PASSWORD_DEFAULT);
		$update = mysqli_prepare($koneksi, 'UPDATE master SET password = ? WHERE id_user = ?');
		if ($update) {
			mysqli_stmt_bind_param($update, 'si', $hashedPassword, $userId);
			mysqli_stmt_execute($update);
			mysqli_stmt_close($update);
		}
	}

	session_regenerate_id(true);
	$_SESSION['login'] = $level;
	$_SESSION['user_id'] = (int) $userId;
	$_SESSION['user_name'] = $name;
	$_SESSION['username'] = $storedUsername;

	authRespond(200, [
		'authenticated' => true,
		'user' => ['id' => (int) $userId, 'name' => $name, 'username' => $storedUsername, 'role' => $level],
	]);
}

if ($method === 'DELETE') {
	$_SESSION = [];
	$cookie = session_get_cookie_params();
	setcookie(session_name(), '', time() - 42000, $cookie['path'], $cookie['domain'], $cookie['secure'], $cookie['httponly']);
	session_destroy();
	authRespond(200, ['authenticated' => false]);
}

header('Allow: GET, POST, DELETE');
authRespond(405, ['error' => 'Method not allowed']);