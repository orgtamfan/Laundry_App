<?php
require_once(__DIR__ . '/../includes/_functions.php');

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

function respond(int $status, array $payload): void
{
	http_response_code($status);
	echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
	exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
	header('Allow: GET');
	respond(405, ['error' => 'Method not allowed']);
}

if (!isset($_SESSION['login'])) {
	respond(401, ['error' => 'Please sign in to view the dashboard.']);
}

if (!$koneksi) {
	respond(500, ['error' => 'Could not connect to the laundry database.']);
}

$sql = "
	SELECT id_order_ck AS id, or_ck_number AS code, nama_pel_ck AS customer,
		'Cuci komplit' AS service, tgl_masuk_ck AS received, tgl_keluar_ck AS due_date,
		tot_bayar AS total
	FROM tb_order_ck
	UNION ALL
	SELECT id_order_cs AS id, or_cs_number AS code, nama_pel_cs AS customer,
		'Cuci satuan' AS service, tgl_masuk_cs AS received, tgl_keluar_cs AS due_date,
		tot_bayar AS total
	FROM tb_order_cs
	UNION ALL
	SELECT id_order_dc AS id, or_dc_number AS code, nama_pel_dc AS customer,
		'Dry clean' AS service, tgl_masuk_dc AS received, tgl_keluar_dc AS due_date,
		tot_bayar AS total
	FROM tb_order_dc
	ORDER BY received DESC, id DESC
";

$result = mysqli_query($koneksi, $sql);
if (!$result) {
	respond(500, ['error' => 'Could not load orders from the database.']);
}

$today = date('Y-m-d');
$orders = [];
$todayCount = 0;
$processingCount = 0;
$readyCount = 0;
$activeValue = 0;

while ($row = mysqli_fetch_assoc($result)) {
	$dueDate = $row['due_date'];
	$status = $dueDate <= $today ? 'Siap diambil' : 'Diproses';
	$total = (int) $row['total'];

	if ($row['received'] === $today) {
		$todayCount++;
	}
	if ($status === 'Siap diambil') {
		$readyCount++;
	} else {
		$processingCount++;
	}

	$activeValue += $total;
	$orders[] = [
		'code' => $row['code'],
		'customer' => $row['customer'],
		'service' => $row['service'],
		'received' => $row['received'],
		'total' => $total,
		'status' => $status,
	];
}

echo json_encode([
	'summary' => [
		'todayOrders' => $todayCount,
		'processingOrders' => $processingCount,
		'readyOrders' => $readyCount,
		'activeValue' => $activeValue,
		'packages' => jmlPaket(),
	],
	'orders' => $orders,
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);