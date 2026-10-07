<?php
require_once(__DIR__ . '/../includes/_functions.php');

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

function operationRespond(int $status, array $payload): void
{
	http_response_code($status);
	echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
	exit();
}

function operationStatement(string $sql, string $types = '', array $values = []): mysqli_stmt
{
	global $koneksi;
	$statement = mysqli_prepare($koneksi, $sql);
	if (!$statement) {
		throw new RuntimeException('Could not prepare the database request.');
	}

	if ($types !== '') {
		$arguments = [$statement, $types];
		foreach ($values as $index => $value) {
			$values[$index] = $value;
			$arguments[] = &$values[$index];
		}
		call_user_func_array('mysqli_stmt_bind_param', $arguments);
	}

	if (!mysqli_stmt_execute($statement)) {
		throw new RuntimeException('Could not execute the database request.');
	}
	return $statement;
}

function operationRows(string $sql, string $types = '', array $values = []): array
{
	$statement = operationStatement($sql, $types, $values);
	$result = mysqli_stmt_get_result($statement);
	if (!$result) {
		mysqli_stmt_close($statement);
		throw new RuntimeException('Could not read the database response.');
	}
	$rows = mysqli_fetch_all($result, MYSQLI_ASSOC);
	mysqli_stmt_close($statement);
	return $rows;
}

function operationTypes(): array
{
	return [
		'ck' => [
			'label' => 'Cuci komplit', 'prefix' => 'CK',
			'packageTable' => 'tb_cuci_komplit', 'packageId' => 'id_ck', 'packageName' => 'nama_paket_ck',
			'packageDuration' => 'waktu_kerja_ck', 'packageQuantity' => 'kuantitas_ck', 'packagePrice' => 'tarif_ck',
			'orderTable' => 'tb_order_ck', 'orderId' => 'id_order_ck', 'orderNumber' => 'or_ck_number',
			'historyTable' => 'tb_riwayat_ck', 'historyId' => 'id_ck',
		],
		'cs' => [
			'label' => 'Cuci satuan', 'prefix' => 'CS',
			'packageTable' => 'tb_cuci_satuan', 'packageId' => 'id_cs', 'packageName' => 'nama_cs',
			'packageDuration' => 'waktu_kerja_cs', 'packageQuantity' => 'kuantitas_cs', 'packagePrice' => 'tarif_cs',
			'orderTable' => 'tb_order_cs', 'orderId' => 'id_order_cs', 'orderNumber' => 'or_cs_number',
			'historyTable' => 'tb_riwayat_cs', 'historyId' => 'id_cs',
		],
		'dc' => [
			'label' => 'Dry clean', 'prefix' => 'DC',
			'packageTable' => 'tb_dry_clean', 'packageId' => 'id_dc', 'packageName' => 'nama_paket_dc',
			'packageDuration' => 'waktu_kerja_dc', 'packageQuantity' => 'kuantitas_dc', 'packagePrice' => 'tarif_dc',
			'orderTable' => 'tb_order_dc', 'orderId' => 'id_order_dc', 'orderNumber' => 'or_dc_number',
			'historyTable' => 'tb_riwayat_dc', 'historyId' => 'id_dc',
		],
	];
}

function operationType(string $key): array
{
	$types = operationTypes();
	if (!isset($types[$key])) {
		operationRespond(422, ['error' => 'Pilih jenis layanan yang valid.']);
	}
	return $types[$key];
}

function operationPackages(): array
{
	$packages = [];
	foreach (operationTypes() as $type => $config) {
		$sql = 'SELECT ' . $config['packageId'] . ' AS id, ' . $config['packageName'] . ' AS name, '
			. $config['packageDuration'] . ' AS duration, ' . $config['packageQuantity'] . ' AS minimum, '
			. $config['packagePrice'] . ' AS price, ' . ($type === 'cs' ? 'keterangan_cs' : "''")
			. ' AS description FROM ' . $config['packageTable'] . ' ORDER BY ' . $config['packageId'] . ' DESC';
		foreach (operationRows($sql) as $row) {
			$packages[] = [
				'id' => (int) $row['id'], 'type' => $type, 'service' => $config['label'],
				'name' => $row['name'], 'duration' => $row['duration'],
				'minimum' => (float) $row['minimum'], 'price' => (int) $row['price'],
				'description' => $row['description'],
			];
		}
	}
	return $packages;
}

function operationOrders(): array
{
	$sql = "
		SELECT 'ck' AS type, id_order_ck AS id, or_ck_number AS code, nama_pel_ck AS customer,
			no_telp_ck AS phone, alamat_ck AS address, jenis_paket_ck AS package,
			wkt_krj_ck AS duration, berat_qty_ck AS quantity, 'kg' AS unit, harga_perkilo AS unitPrice,
			tgl_masuk_ck AS received, tgl_keluar_ck AS dueDate, tot_bayar AS total, keterangan_ck AS notes
		FROM tb_order_ck
		UNION ALL
		SELECT 'cs', id_order_cs, or_cs_number, nama_pel_cs, no_telp_cs, alamat_cs, jenis_paket_cs,
			wkt_krj_cs, jml_pcs, 'pcs', harga_perpcs, tgl_masuk_cs, tgl_keluar_cs, tot_bayar, keterangan_cs
		FROM tb_order_cs
		UNION ALL
		SELECT 'dc', id_order_dc, or_dc_number, nama_pel_dc, no_telp_dc, alamat_dc, jenis_paket_dc,
			wkt_krj_dc, berat_qty_dc, 'kg', harga_perkilo, tgl_masuk_dc, tgl_keluar_dc, tot_bayar, keterangan_dc
		FROM tb_order_dc
		ORDER BY received DESC, id DESC
	";
	$today = date('Y-m-d');
	$orders = [];
	foreach (operationRows($sql) as $row) {
		$row['id'] = (int) $row['id'];
		$row['quantity'] = (float) $row['quantity'];
		$row['unitPrice'] = (int) $row['unitPrice'];
		$row['total'] = (int) $row['total'];
		$row['service'] = operationType($row['type'])['label'];
		$row['status'] = $row['dueDate'] <= $today ? 'Siap diambil' : 'Diproses';
		$orders[] = $row;
	}
	return $orders;
}

function operationEmployees(): array
{
	return operationRows(
		'SELECT id_user AS id, nama AS name, email, username, level AS role FROM master ORDER BY id_user DESC'
	);
}

function operationHistory(): array
{
	$sql = "
		SELECT 'ck' AS type, id_ck AS id, or_number AS code, pelanggan AS customer, no_telp AS phone,
			alamat AS address, jns_paket AS package, wkt_kerja AS duration, berat AS quantity,
			'kg' AS unit, h_perkilo AS unitPrice, tgl_msk AS received, tgl_klr AS dueDate,
			total, nominal_bayar AS paid, kembalian AS changeAmount, status, keterangan AS notes
		FROM tb_riwayat_ck
		UNION ALL
		SELECT 'cs', id_cs, or_number, pelanggan, no_telp, alamat, jns_paket, wkt_kerja, jml_pcs,
			'pcs', h_perpcs, tgl_msk, tgl_klr, total, nominal_bayar, kembalian, status, keterangan
		FROM tb_riwayat_cs
		UNION ALL
		SELECT 'dc', id_dc, or_number, pelanggan, no_telp, alamat, jns_paket, wkt_kerja, berat,
			'kg', h_perkilo, tgl_msk, tgl_klr, total, nominal_bayar, kembalian, status, keterangan
		FROM tb_riwayat_dc
		ORDER BY id DESC
	";
	$history = [];
	foreach (operationRows($sql) as $row) {
		$row['id'] = (int) $row['id'];
		$row['quantity'] = (float) $row['quantity'];
		$row['unitPrice'] = (int) $row['unitPrice'];
		$row['total'] = (int) $row['total'];
		$row['paid'] = (int) $row['paid'];
		$row['changeAmount'] = (int) $row['changeAmount'];
		$row['service'] = operationType($row['type'])['label'];
		$history[] = $row;
	}
	return $history;
}

function operationSaveOrder(array $data): void
{
	$type = (string) ($data['type'] ?? '');
	$config = operationType($type);
	$customer = trim((string) ($data['customer'] ?? ''));
	$phone = trim((string) ($data['phone'] ?? ''));
	$address = trim((string) ($data['address'] ?? ''));
	$notes = trim((string) ($data['notes'] ?? ''));
	$packageId = filter_var($data['packageId'] ?? null, FILTER_VALIDATE_INT);
	$quantity = filter_var($data['quantity'] ?? null, FILTER_VALIDATE_FLOAT);
	$received = (string) ($data['received'] ?? '');
	$dueDate = (string) ($data['dueDate'] ?? '');

	if ($customer === '' || $packageId === false || $packageId < 1 || $quantity === false || $quantity <= 0
		|| !preg_match('/^\d{4}-\d{2}-\d{2}$/', $received)
		|| !preg_match('/^\d{4}-\d{2}-\d{2}$/', $dueDate)
		|| $dueDate < $received) {
		operationRespond(422, ['error' => 'Lengkapi pelanggan, paket, jumlah, dan tanggal order dengan benar.']);
	}
	if ($type === 'cs' && (floor((float) $quantity) !== (float) $quantity || $quantity < 1)) {
		operationRespond(422, ['error' => 'Jumlah pakaian harus berupa bilangan bulat positif.']);
	}

	$packageRows = operationRows(
		'SELECT ' . $config['packageName'] . ' AS name, ' . $config['packageDuration'] . ' AS duration, '
		. $config['packagePrice'] . ' AS price FROM ' . $config['packageTable'] . ' WHERE '
		. $config['packageId'] . ' = ? LIMIT 1',
		'i', [$packageId]
	);
	if (!$packageRows) {
		operationRespond(422, ['error' => 'Paket yang dipilih sudah tidak tersedia.']);
	}
	$package = $packageRows[0];
	$price = (int) $package['price'];
	$total = (int) round((float) $quantity * $price);
	$code = $config['prefix'] . '-' . strtoupper(bin2hex(random_bytes(4)));

	if ($type === 'cs') {
		$sql = 'INSERT INTO tb_order_cs (or_cs_number,nama_pel_cs,no_telp_cs,alamat_cs,jenis_paket_cs,wkt_krj_cs,jml_pcs,harga_perpcs,tgl_masuk_cs,tgl_keluar_cs,tot_bayar,keterangan_cs) VALUES (?,?,?,?,?,?,?,?,?,?,?,?)';
		$values = [$code, $customer, $phone, $address, $package['name'], $package['duration'], (int) $quantity, $price, $received, $dueDate, $total, $notes];
		$types = 'ssssssiissis';
	} elseif ($type === 'ck') {
		$sql = 'INSERT INTO tb_order_ck (or_ck_number,nama_pel_ck,no_telp_ck,alamat_ck,jenis_paket_ck,wkt_krj_ck,berat_qty_ck,harga_perkilo,tgl_masuk_ck,tgl_keluar_ck,tot_bayar,keterangan_ck) VALUES (?,?,?,?,?,?,?,?,?,?,?,?)';
		$values = [$code, $customer, $phone, $address, $package['name'], $package['duration'], (float) $quantity, $price, $received, $dueDate, $total, $notes];
		$types = 'ssssssdissis';
	} else {
		$sql = 'INSERT INTO tb_order_dc (or_dc_number,nama_pel_dc,no_telp_dc,alamat_dc,jenis_paket_dc,wkt_krj_dc,berat_qty_dc,harga_perkilo,tgl_masuk_dc,tgl_keluar_dc,tot_bayar,keterangan_dc) VALUES (?,?,?,?,?,?,?,?,?,?,?,?)';
		$values = [$code, $customer, $phone, $address, $package['name'], $package['duration'], (float) $quantity, $price, $received, $dueDate, $total, $notes];
		$types = 'ssssssdissis';
	}

	operationStatement($sql, $types, $values);
	operationRespond(201, ['message' => 'Order berhasil dibuat.', 'code' => $code]);
}

function operationCancelOrder(array $data): void
{
	$type = (string) ($data['type'] ?? '');
	$config = operationType($type);
	$code = trim((string) ($data['code'] ?? ''));
	if ($code === '') operationRespond(422, ['error' => 'Nomor order tidak valid.']);
	$statement = operationStatement(
		'DELETE FROM ' . $config['orderTable'] . ' WHERE ' . $config['orderNumber'] . ' = ?',
		's', [$code]
	);
	$deleted = mysqli_stmt_affected_rows($statement);
	mysqli_stmt_close($statement);
	if (!$deleted) operationRespond(404, ['error' => 'Order tidak ditemukan.']);
	operationRespond(200, ['message' => 'Order berhasil dibatalkan.']);
}

function operationPayOrder(array $data): void
{
	$type = (string) ($data['type'] ?? '');
	$config = operationType($type);
	$code = trim((string) ($data['code'] ?? ''));
	$paid = filter_var($data['paid'] ?? null, FILTER_VALIDATE_INT);
	if ($code === '' || $paid === false || $paid < 0) {
		operationRespond(422, ['error' => 'Nomor order atau nominal pembayaran tidak valid.']);
	}

	global $koneksi;
	mysqli_begin_transaction($koneksi);
	try {
		$statement = operationStatement(
			'SELECT * FROM ' . $config['orderTable'] . ' WHERE ' . $config['orderNumber'] . ' = ? FOR UPDATE',
			's', [$code]
		);
		$result = mysqli_stmt_get_result($statement);
		$order = $result ? mysqli_fetch_assoc($result) : null;
		mysqli_stmt_close($statement);
		if (!$order) {
			mysqli_rollback($koneksi);
			operationRespond(404, ['error' => 'Order tidak ditemukan atau sudah dibayar.']);
		}

		$total = (int) $order['tot_bayar'];
		if ($paid < $total) {
			mysqli_rollback($koneksi);
			operationRespond(422, ['error' => 'Nominal pembayaran kurang dari total order.']);
		}
		$change = $paid - $total;
		$today = formatDate($order[$type === 'cs' ? 'tgl_masuk_cs' : ($type === 'ck' ? 'tgl_masuk_ck' : 'tgl_masuk_dc')]);
		$due = formatDate($order[$type === 'cs' ? 'tgl_keluar_cs' : ($type === 'ck' ? 'tgl_keluar_ck' : 'tgl_keluar_dc')]);

		if ($type === 'ck') {
			$historySql = 'INSERT INTO tb_riwayat_ck (or_number,pelanggan,no_telp,alamat,jns_paket,wkt_kerja,berat,h_perkilo,tgl_msk,tgl_klr,total,nominal_bayar,kembalian,status,keterangan) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)';
			$historyValues = [$code,$order['nama_pel_ck'],$order['no_telp_ck'],$order['alamat_ck'],$order['jenis_paket_ck'],$order['wkt_krj_ck'],(float) $order['berat_qty_ck'],(int) $order['harga_perkilo'],$today,$due,$total,$paid,$change,'Sukses',$order['keterangan_ck']];
			$historyTypes = 'ssssssdissiiiss';
		} elseif ($type === 'cs') {
			$historySql = 'INSERT INTO tb_riwayat_cs (or_number,pelanggan,no_telp,alamat,jns_paket,wkt_kerja,jml_pcs,h_perpcs,tgl_msk,tgl_klr,total,nominal_bayar,kembalian,status,keterangan) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)';
			$historyValues = [$code,$order['nama_pel_cs'],$order['no_telp_cs'],$order['alamat_cs'],$order['jenis_paket_cs'],$order['wkt_krj_cs'],(int) $order['jml_pcs'],(int) $order['harga_perpcs'],$today,$due,$total,$paid,$change,'Sukses',$order['keterangan_cs']];
			$historyTypes = 'ssssssiissiiiss';
		} else {
			$historySql = 'INSERT INTO tb_riwayat_dc (or_number,pelanggan,no_telp,alamat,jns_paket,wkt_kerja,berat,h_perkilo,tgl_msk,tgl_klr,total,nominal_bayar,kembalian,status,keterangan) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)';
			$historyValues = [$code,$order['nama_pel_dc'],$order['no_telp_dc'],$order['alamat_dc'],$order['jenis_paket_dc'],$order['wkt_krj_dc'],(float) $order['berat_qty_dc'],(int) $order['harga_perkilo'],$today,$due,$total,$paid,$change,'Sukses',$order['keterangan_dc']];
			$historyTypes = 'ssssssdissiiiss';
		}

		operationStatement($historySql, $historyTypes, $historyValues);
		operationStatement(
			'DELETE FROM ' . $config['orderTable'] . ' WHERE ' . $config['orderNumber'] . ' = ?',
			's', [$code]
		);
		mysqli_commit($koneksi);
		operationRespond(200, ['message' => 'Pembayaran berhasil dicatat.', 'changeAmount' => $change]);
	} catch (Throwable $error) {
		mysqli_rollback($koneksi);
		throw $error;
	}
}

function operationSavePackage(array $data): void
{
	$type = (string) ($data['type'] ?? '');
	$config = operationType($type);
	$id = filter_var($data['id'] ?? 0, FILTER_VALIDATE_INT);
	$name = trim((string) ($data['name'] ?? ''));
	$duration = trim((string) ($data['duration'] ?? ''));
	$minimum = filter_var($data['minimum'] ?? null, FILTER_VALIDATE_FLOAT);
	$price = filter_var($data['price'] ?? null, FILTER_VALIDATE_INT);
	$description = trim((string) ($data['description'] ?? '-'));
	if ($id === false || $name === '' || $duration === '' || $minimum === false || $minimum < 0 || $price === false || $price < 0) {
		operationRespond(422, ['error' => 'Lengkapi nama, durasi, jumlah minimum, dan tarif paket dengan benar.']);
	}
	if ($type === 'cs' && floor((float) $minimum) !== (float) $minimum) {
		operationRespond(422, ['error' => 'Jumlah minimum pakaian harus berupa bilangan bulat.']);
	}

	if ($type === 'cs') {
		$columns = [$config['packageName'], $config['packageDuration'], $config['packageQuantity'], $config['packagePrice'], 'keterangan_cs'];
		$values = [$name, $duration, (int) $minimum, $price, $description];
		$types = 'ssiis';
	} else {
		$columns = [$config['packageName'], $config['packageDuration'], $config['packageQuantity'], $config['packagePrice']];
		$values = [$name, $duration, $minimum, $price];
		$types = 'ssdi';
	}

	if ($id > 0) {
		$assignments = implode(',', array_map(static fn($column) => $column . ' = ?', $columns));
		$values[] = $id;
		$types .= 'i';
		$sql = 'UPDATE ' . $config['packageTable'] . ' SET ' . $assignments . ' WHERE ' . $config['packageId'] . ' = ?';
	} else {
		$sql = 'INSERT INTO ' . $config['packageTable'] . ' (' . implode(',', $columns) . ') VALUES (' . implode(',', array_fill(0, count($columns), '?')) . ')';
	}
	$statement = operationStatement($sql, $types, $values);
	$changed = mysqli_stmt_affected_rows($statement);
	mysqli_stmt_close($statement);
	if (!$changed && $id > 0) {
		$changed = count(operationRows(
			'SELECT ' . $config['packageId'] . ' FROM ' . $config['packageTable'] . ' WHERE ' . $config['packageId'] . ' = ?',
			'i', [$id]
		));
	}
	if (!$changed) operationRespond(404, ['error' => 'Paket tidak ditemukan.']);
	operationRespond(200, ['message' => 'Paket berhasil disimpan.']);
}

function operationDeletePackage(array $data): void
{
	$type = (string) ($data['type'] ?? '');
	$config = operationType($type);
	$id = filter_var($data['id'] ?? null, FILTER_VALIDATE_INT);
	if ($id === false || $id < 1) operationRespond(422, ['error' => 'Paket tidak valid.']);
	$statement = operationStatement(
		'DELETE FROM ' . $config['packageTable'] . ' WHERE ' . $config['packageId'] . ' = ?',
		'i', [$id]
	);
	$deleted = mysqli_stmt_affected_rows($statement);
	mysqli_stmt_close($statement);
	if (!$deleted) operationRespond(404, ['error' => 'Paket tidak ditemukan.']);
	operationRespond(200, ['message' => 'Paket berhasil dihapus.']);
}

function operationSaveEmployee(array $data): void
{
	$id = filter_var($data['id'] ?? 0, FILTER_VALIDATE_INT);
	$name = trim((string) ($data['name'] ?? ''));
	$email = trim((string) ($data['email'] ?? ''));
	$username = trim((string) ($data['username'] ?? ''));
	$password = (string) ($data['password'] ?? '');
	$role = (string) ($data['role'] ?? 'Karyawan');
	if ($id === false || $name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || $username === ''
		|| !in_array($role, ['Admin', 'User', 'Karyawan'], true) || ($id === 0 && strlen($password) < 6)
		|| ($password !== '' && strlen($password) < 6)) {
		operationRespond(422, ['error' => 'Periksa nama, email, username, peran, dan password (minimal 6 karakter).']);
	}

	$duplicates = operationRows(
		'SELECT id_user FROM master WHERE (username = ? OR email = ?) AND id_user <> ? LIMIT 1',
		'ssi', [$username, $email, $id]
	);
	if ($duplicates) operationRespond(409, ['error' => 'Username atau email sudah digunakan.']);

	if ($id > 0) {
		if ($password !== '') {
			$hashed = password_hash($password, PASSWORD_DEFAULT);
			$sql = 'UPDATE master SET nama = ?, email = ?, username = ?, level = ?, password = ? WHERE id_user = ?';
			$types = 'sssssi';
			$values = [$name, $email, $username, $role, $hashed, $id];
		} else {
			$sql = 'UPDATE master SET nama = ?, email = ?, username = ?, level = ? WHERE id_user = ?';
			$types = 'ssssi';
			$values = [$name, $email, $username, $role, $id];
		}
	} else {
		$hashed = password_hash($password, PASSWORD_DEFAULT);
		$sql = 'INSERT INTO master (nama,email,username,password,level) VALUES (?,?,?,?,?)';
		$types = 'sssss';
		$values = [$name, $email, $username, $hashed, $role];
	}
	$statement = operationStatement($sql, $types, $values);
	$changed = mysqli_stmt_affected_rows($statement);
	mysqli_stmt_close($statement);
	if (!$changed && $id > 0) {
		$changed = count(operationRows('SELECT id_user FROM master WHERE id_user = ?', 'i', [$id]));
	}
	if (!$changed) operationRespond(404, ['error' => 'Akun tidak ditemukan.']);
	operationRespond(200, ['message' => 'Data karyawan berhasil disimpan.']);
}

function operationDeleteEmployee(array $data): void
{
	$id = filter_var($data['id'] ?? null, FILTER_VALIDATE_INT);
	if ($id === false || $id < 1) operationRespond(422, ['error' => 'Akun tidak valid.']);
	if ($id === (int) ($_SESSION['user_id'] ?? 0)) {
		operationRespond(422, ['error' => 'Akun yang sedang digunakan tidak dapat dihapus.']);
	}
	$statement = operationStatement('DELETE FROM master WHERE id_user = ?', 'i', [$id]);
	$deleted = mysqli_stmt_affected_rows($statement);
	mysqli_stmt_close($statement);
	if (!$deleted) operationRespond(404, ['error' => 'Akun tidak ditemukan.']);
	operationRespond(200, ['message' => 'Akun berhasil dihapus.']);
}

if (!in_array($_SESSION['login'] ?? null, ['Admin', 'User', 'Karyawan'], true)) {
	operationRespond(401, ['error' => 'Please sign in to continue.']);
}
if (!$koneksi) operationRespond(500, ['error' => 'Could not connect to the laundry database.']);

try {
	if ($_SERVER['REQUEST_METHOD'] === 'GET') {
		$resource = $_GET['resource'] ?? 'orders';
		switch ($resource) {
			case 'orders': operationRespond(200, ['items' => operationOrders()]);
			case 'packages': operationRespond(200, ['items' => operationPackages()]);
			case 'employees':
				if ($_SESSION['login'] !== 'Admin') {
					operationRespond(403, ['error' => 'Administrator access is required.']);
				}
				operationRespond(200, ['items' => operationEmployees()]);
			case 'history': operationRespond(200, ['items' => operationHistory()]);
			default: operationRespond(404, ['error' => 'Resource not found.']);
		}
	}

	if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
		header('Allow: GET, POST');
		operationRespond(405, ['error' => 'Method not allowed.']);
	}

	$data = json_decode(file_get_contents('php://input'), true);
	if (!is_array($data)) operationRespond(400, ['error' => 'Invalid JSON request.']);
	$action = $data['action'] ?? '';
	if (in_array($action, ['package.save', 'package.delete', 'employee.save', 'employee.delete'], true)
		&& $_SESSION['login'] !== 'Admin') {
		operationRespond(403, ['error' => 'Administrator access is required for this action.']);
	}
	switch ($action) {
		case 'order.create': operationSaveOrder($data);
		case 'order.cancel': operationCancelOrder($data);
		case 'order.pay': operationPayOrder($data);
		case 'package.save': operationSavePackage($data);
		case 'package.delete': operationDeletePackage($data);
		case 'employee.save': operationSaveEmployee($data);
		case 'employee.delete': operationDeleteEmployee($data);
		default: operationRespond(422, ['error' => 'Action not supported.']);
	}
} catch (Throwable $error) {
	if (isset($koneksi) && $koneksi) {
		mysqli_rollback($koneksi);
	}
	operationRespond(500, ['error' => 'Terjadi kesalahan saat memproses permintaan.']);
}