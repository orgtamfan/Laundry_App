CREATE DATABASE IF NOT EXISTS `laundry`
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE `laundry`;

SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS `tb_riwayat_dc`;
DROP TABLE IF EXISTS `tb_riwayat_cs`;
DROP TABLE IF EXISTS `tb_riwayat_ck`;
DROP TABLE IF EXISTS `tb_order_dc`;
DROP TABLE IF EXISTS `tb_order_cs`;
DROP TABLE IF EXISTS `tb_order_ck`;
DROP TABLE IF EXISTS `tb_dry_clean`;
DROP TABLE IF EXISTS `tb_cuci_satuan`;
DROP TABLE IF EXISTS `tb_cuci_komplit`;
DROP TABLE IF EXISTS `master`;

SET FOREIGN_KEY_CHECKS = 1;

CREATE TABLE `master` (
  `id_user` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `nama` VARCHAR(100) NOT NULL,
  `email` VARCHAR(100) NOT NULL,
  `username` VARCHAR(50) NOT NULL,
  `password` VARCHAR(255) NOT NULL,
  `level` VARCHAR(20) NOT NULL,
  PRIMARY KEY (`id_user`),
  UNIQUE KEY `uk_master_email` (`email`),
  UNIQUE KEY `uk_master_username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `tb_cuci_komplit` (
  `id_ck` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `nama_paket_ck` VARCHAR(100) NOT NULL,
  `waktu_kerja_ck` VARCHAR(50) NOT NULL,
  `kuantitas_ck` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `tarif_ck` INT UNSIGNED NOT NULL DEFAULT 0,
  PRIMARY KEY (`id_ck`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `tb_dry_clean` (
  `id_dc` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `nama_paket_dc` VARCHAR(100) NOT NULL,
  `waktu_kerja_dc` VARCHAR(50) NOT NULL,
  `kuantitas_dc` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `tarif_dc` INT UNSIGNED NOT NULL DEFAULT 0,
  PRIMARY KEY (`id_dc`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `tb_cuci_satuan` (
  `id_cs` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `nama_cs` VARCHAR(100) NOT NULL,
  `waktu_kerja_cs` VARCHAR(50) NOT NULL,
  `kuantitas_cs` INT UNSIGNED NOT NULL DEFAULT 0,
  `tarif_cs` INT UNSIGNED NOT NULL DEFAULT 0,
  `keterangan_cs` VARCHAR(255) NOT NULL DEFAULT '-',
  PRIMARY KEY (`id_cs`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `tb_order_ck` (
  `id_order_ck` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `or_ck_number` VARCHAR(30) NOT NULL,
  `nama_pel_ck` VARCHAR(100) NOT NULL,
  `no_telp_ck` VARCHAR(30) NOT NULL,
  `alamat_ck` TEXT NOT NULL,
  `jenis_paket_ck` VARCHAR(100) NOT NULL,
  `wkt_krj_ck` VARCHAR(50) NOT NULL,
  `berat_qty_ck` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `harga_perkilo` INT UNSIGNED NOT NULL DEFAULT 0,
  `tgl_masuk_ck` DATE NOT NULL,
  `tgl_keluar_ck` DATE NOT NULL,
  `tot_bayar` INT UNSIGNED NOT NULL DEFAULT 0,
  `keterangan_ck` TEXT NOT NULL,
  PRIMARY KEY (`id_order_ck`),
  UNIQUE KEY `uk_tb_order_ck_number` (`or_ck_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `tb_order_dc` (
  `id_order_dc` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `or_dc_number` VARCHAR(30) NOT NULL,
  `nama_pel_dc` VARCHAR(100) NOT NULL,
  `no_telp_dc` VARCHAR(30) NOT NULL,
  `alamat_dc` TEXT NOT NULL,
  `jenis_paket_dc` VARCHAR(100) NOT NULL,
  `wkt_krj_dc` VARCHAR(50) NOT NULL,
  `berat_qty_dc` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `harga_perkilo` INT UNSIGNED NOT NULL DEFAULT 0,
  `tgl_masuk_dc` DATE NOT NULL,
  `tgl_keluar_dc` DATE NOT NULL,
  `tot_bayar` INT UNSIGNED NOT NULL DEFAULT 0,
  `keterangan_dc` TEXT NOT NULL,
  PRIMARY KEY (`id_order_dc`),
  UNIQUE KEY `uk_tb_order_dc_number` (`or_dc_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `tb_order_cs` (
  `id_order_cs` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `or_cs_number` VARCHAR(30) NOT NULL,
  `nama_pel_cs` VARCHAR(100) NOT NULL,
  `no_telp_cs` VARCHAR(30) NOT NULL,
  `alamat_cs` TEXT NOT NULL,
  `jenis_paket_cs` VARCHAR(100) NOT NULL,
  `wkt_krj_cs` VARCHAR(50) NOT NULL,
  `jml_pcs` INT UNSIGNED NOT NULL DEFAULT 0,
  `harga_perpcs` INT UNSIGNED NOT NULL DEFAULT 0,
  `tgl_masuk_cs` DATE NOT NULL,
  `tgl_keluar_cs` DATE NOT NULL,
  `tot_bayar` INT UNSIGNED NOT NULL DEFAULT 0,
  `keterangan_cs` TEXT NOT NULL,
  PRIMARY KEY (`id_order_cs`),
  UNIQUE KEY `uk_tb_order_cs_number` (`or_cs_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `tb_riwayat_ck` (
  `id_ck` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `or_number` VARCHAR(30) NOT NULL,
  `pelanggan` VARCHAR(100) NOT NULL,
  `no_telp` VARCHAR(30) NOT NULL,
  `alamat` TEXT NOT NULL,
  `jns_paket` VARCHAR(100) NOT NULL,
  `wkt_kerja` VARCHAR(50) NOT NULL,
  `berat` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `h_perkilo` INT UNSIGNED NOT NULL DEFAULT 0,
  `tgl_msk` VARCHAR(50) NOT NULL,
  `tgl_klr` VARCHAR(50) NOT NULL,
  `total` INT UNSIGNED NOT NULL DEFAULT 0,
  `nominal_bayar` INT UNSIGNED NOT NULL DEFAULT 0,
  `kembalian` INT UNSIGNED NOT NULL DEFAULT 0,
  `status` VARCHAR(20) NOT NULL DEFAULT 'Sukses',
  `keterangan` TEXT NOT NULL,
  PRIMARY KEY (`id_ck`),
  KEY `idx_riwayat_ck_order` (`or_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `tb_riwayat_cs` (
  `id_cs` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `or_number` VARCHAR(30) NOT NULL,
  `pelanggan` VARCHAR(100) NOT NULL,
  `no_telp` VARCHAR(30) NOT NULL,
  `alamat` TEXT NOT NULL,
  `jns_paket` VARCHAR(100) NOT NULL,
  `wkt_kerja` VARCHAR(50) NOT NULL,
  `jml_pcs` INT UNSIGNED NOT NULL DEFAULT 0,
  `h_perpcs` INT UNSIGNED NOT NULL DEFAULT 0,
  `tgl_msk` VARCHAR(50) NOT NULL,
  `tgl_klr` VARCHAR(50) NOT NULL,
  `total` INT UNSIGNED NOT NULL DEFAULT 0,
  `nominal_bayar` INT UNSIGNED NOT NULL DEFAULT 0,
  `kembalian` INT UNSIGNED NOT NULL DEFAULT 0,
  `status` VARCHAR(20) NOT NULL DEFAULT 'Sukses',
  `keterangan` TEXT NOT NULL,
  PRIMARY KEY (`id_cs`),
  KEY `idx_riwayat_cs_order` (`or_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `tb_riwayat_dc` (
  `id_dc` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `or_number` VARCHAR(30) NOT NULL,
  `pelanggan` VARCHAR(100) NOT NULL,
  `no_telp` VARCHAR(30) NOT NULL,
  `alamat` TEXT NOT NULL,
  `jns_paket` VARCHAR(100) NOT NULL,
  `wkt_kerja` VARCHAR(50) NOT NULL,
  `berat` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `h_perkilo` INT UNSIGNED NOT NULL DEFAULT 0,
  `tgl_msk` VARCHAR(50) NOT NULL,
  `tgl_klr` VARCHAR(50) NOT NULL,
  `total` INT UNSIGNED NOT NULL DEFAULT 0,
  `nominal_bayar` INT UNSIGNED NOT NULL DEFAULT 0,
  `kembalian` INT UNSIGNED NOT NULL DEFAULT 0,
  `status` VARCHAR(20) NOT NULL DEFAULT 'Sukses',
  `keterangan` TEXT NOT NULL,
  PRIMARY KEY (`id_dc`),
  KEY `idx_riwayat_dc_order` (`or_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Optional starter data
INSERT INTO `master` (`nama`, `email`, `username`, `password`, `level`) VALUES
('Administrator', 'admin@rumahlaundry.local', 'admin', 'admin123', 'Admin'),
('User Demo', 'user@rumahlaundry.local', 'user', 'user123', 'User');

INSERT INTO `tb_cuci_komplit` (`nama_paket_ck`, `waktu_kerja_ck`, `kuantitas_ck`, `tarif_ck`) VALUES
('Cuci Komplit Reguler', '2 Hari', 1.00, 7000),
('Cuci Komplit Express', '1 Hari', 1.00, 12000);

INSERT INTO `tb_dry_clean` (`nama_paket_dc`, `waktu_kerja_dc`, `kuantitas_dc`, `tarif_dc`) VALUES
('Dry Clean Reguler', '3 Hari', 1.00, 10000),
('Dry Clean Express', '1 Hari', 1.00, 15000);

INSERT INTO `tb_cuci_satuan` (`nama_cs`, `waktu_kerja_cs`, `kuantitas_cs`, `tarif_cs`, `keterangan_cs`) VALUES
('Kemeja', '1 Hari', 1, 5000, '-'),
('Jaket', '2 Hari', 1, 12000, '-'),
('Selimut', '2 Hari', 1, 15000, '-');
