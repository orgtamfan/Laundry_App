<?php 
	require_once(__DIR__ . '/../includes/_functions.php');
	if (!isset($_SESSION['login']) || $_SESSION['login'] != 'Admin') {
		// Jika sesi 'login' tidak ada atau bernilai false, arahkan pengguna kembali ke halaman login
		header("Location: " . url('login.php'));
		exit();
	}
	require_once(__DIR__ . '/../includes/_header.php');
?>
<div id="main" class="main-content">
		<div class="container">
			<div class="baris">
				<div class="selamat-datang">
					<div class="col-header">
						<p class="judul-sm">Selamat Datang <span></span></p>
						<h2 class="judul-md">Dashboard</h2>
					</div>

					<div class="col-header txt-right">
						<a href="<?=url('order/order.php')?>" class="btn-lg bg-primary">+ Order Baru</a>
					</div>	
				</div>
			</div>

			<div class="baris">
				<div class="col col-4">
					<div class="card">
						<div class="card-body">
							<div class="card-panel">
								<div class="panel-header">
									<p>Jumlah Karyawan</p>
									<?php 
									$host	= 'localhost';
									$user = 'root';
									$pass	= '';
									$db	= 'laundry';
									
									// Koneksi ke Database
									$koneksi = mysqli_connect($host,$user,$pass,$db);
									
									$query = "SELECT COUNT(*) as total_karyawan FROM master WHERE level = 'Karyawan'";
									$result = mysqli_query($koneksi, $query);

									if ($result) {
										$row = mysqli_fetch_assoc($result);
										$totalKaryawan = $row['total_karyawan'];

										echo "<h2>Total Karyawan: $totalKaryawan</h2>";
									} else {
										echo "Error: " . mysqli_error($koneksi);
									}

									?>
								</div>
								<div class="panel-icon">
									<img src="<?=url('_assets/img/team.png')?>" alt="karyawan" height="48">
								</div>
							</div>
						</div>
					</div>
				</div>

				<div class="col col-4">
					<div class="card">
						<div class="card-body">
							<div class="card-panel">
								<div class="panel-header">
									<p>Total Order</p>
									<h2><?= jmlOrder(); ?></h2>
								</div>
								
								<div class="panel-icon">
									<img src="<?=url('_assets/img/total_order.png')?>" alt="order" height="48">
								</div>
							</div>
						</div>
					</div>
				</div>

				<div class="col col-4">
					<div class="card">
						<div class="card-body">
							<div class="card-panel">
								<div class="panel-header">
									<p>Jumlah Paket Tersedia</p>
									<h2><?= jmlPaket(); ?></h2>
								</div>

								<div class="panel-icon">
									<img src="<?=url('_assets/img/jumlah_paket.png')?>" alt="paket" height="48">
								</div>
							</div>
							
						</div>
					</div>
				</div>
			</div>

			<?php require_once(__DIR__ . '/../includes/_map.php'); ?>

			<!-- Daftar Order Cuci Komplit -->
			<div class="baris">
				<?php require_once(__DIR__ . '/../daftar_order/daf_or_ck.php');?>
			</div>

			<!-- Daftar Order Cuci Kering/Dry Clean -->
			<div class="baris">
				<?php require_once(__DIR__ . '/../daftar_order/daf_or_dc.php');?>
			</div>

			<!-- Daftar Order Cuci Satuan -->
			<div class="baris">
				<?php require_once(__DIR__ . '/../daftar_order/daf_or_cs.php');?>
			</div>

		</div>
	</div>

<?php require_once(__DIR__ . '/../includes/_footer.php'); ?>
