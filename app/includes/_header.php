<?php 
	require_once(__DIR__ . '/_functions.php');
?>

<!DOCTYPE html>
<html>
<head>
	<title>Rumah Laundry | Dashboard</title>
	<link rel="stylesheet" href="<?=url('_assets/css/style.css')?>">
	<link rel="shortcut icon" href="<?=url('_assets/img/logo/favicon.svg')?>" type="image/x-icon">
</head>
<body>

	<header>
		<nav>
			<div class="logo">
				<a href="<?=homeUrl()?>" class="brand-link" aria-label="Rumah Laundry Home">
					<img src="<?=url('_assets/img/logo/favicon.svg')?>" alt="Rumah Laundry Logo">
					<span class="brand-name">RUMAH <strong>LAUNDRY</strong></span>
				</a>
			</div>
			<ul class="nav-menu">
				<li>
				
					<span id="">Menu</span>
					<ul class="dropdown-menu">
						<li><a href="<?=url('about.php')?>">Tentang Kami</a></li>
						<li><a href="<?=url('logout.php')?>">Logout</a></li>
					</ul>
				</li>
			</ul>
		</nav>
		<div id="nav-mini">
			<a href="<?=homeUrl()?>" class="link-nav">Home</a>
			<?php if (isset($_SESSION['login']) && $_SESSION['login'] === 'Admin') : ?>
				<a href="<?=url('karyawan/karyawan.php')?>" class="link-nav">Karyawan</a>
			<?php endif ?>
			<a href="<?=url('riwayat_transaksi/riwayat.php')?>" class="link-nav">Riwayat Transaksi</a>
			<a href="<?=url('paket/paket.php')?>" class="link-nav">Daftar Paket</a>
		</div>
	</header>
