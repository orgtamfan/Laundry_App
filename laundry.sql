-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: May 05, 2026 at 02:44 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `laundry`
--

-- --------------------------------------------------------

--
-- Table structure for table `master`
--

CREATE TABLE `master` (
  `id_user` int(10) UNSIGNED NOT NULL,
  `nama` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `level` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `master`
--

INSERT INTO `master` (`id_user`, `nama`, `email`, `username`, `password`, `level`) VALUES
(1, 'Administrator', 'admin@rumahlaundry.local', 'admin', 'admin123', 'Admin'),
(2, 'User Demo', 'user@rumahlaundry.local', 'user', 'user123', 'User');

-- --------------------------------------------------------

--
-- Table structure for table `tb_cuci_komplit`
--

CREATE TABLE `tb_cuci_komplit` (
  `id_ck` int(10) UNSIGNED NOT NULL,
  `nama_paket_ck` varchar(100) NOT NULL,
  `waktu_kerja_ck` varchar(50) NOT NULL,
  `kuantitas_ck` decimal(10,2) NOT NULL DEFAULT 0.00,
  `tarif_ck` int(10) UNSIGNED NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `tb_cuci_komplit`
--

INSERT INTO `tb_cuci_komplit` (`id_ck`, `nama_paket_ck`, `waktu_kerja_ck`, `kuantitas_ck`, `tarif_ck`) VALUES
(1, 'Cuci Komplit Reguler', '2 Hari', 1.00, 7000),
(2, 'Cuci Komplit Express', '1 Hari', 1.00, 12000);

-- --------------------------------------------------------

--
-- Table structure for table `tb_cuci_satuan`
--

CREATE TABLE `tb_cuci_satuan` (
  `id_cs` int(10) UNSIGNED NOT NULL,
  `nama_cs` varchar(100) NOT NULL,
  `waktu_kerja_cs` varchar(50) NOT NULL,
  `kuantitas_cs` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `tarif_cs` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `keterangan_cs` varchar(255) NOT NULL DEFAULT '-'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `tb_cuci_satuan`
--

INSERT INTO `tb_cuci_satuan` (`id_cs`, `nama_cs`, `waktu_kerja_cs`, `kuantitas_cs`, `tarif_cs`, `keterangan_cs`) VALUES
(1, 'Kemeja', '1 Hari', 1, 5000, '-'),
(2, 'Jaket', '2 Hari', 1, 12000, '-'),
(3, 'Selimut', '2 Hari', 1, 15000, '-');

-- --------------------------------------------------------

--
-- Table structure for table `tb_dry_clean`
--

CREATE TABLE `tb_dry_clean` (
  `id_dc` int(10) UNSIGNED NOT NULL,
  `nama_paket_dc` varchar(100) NOT NULL,
  `waktu_kerja_dc` varchar(50) NOT NULL,
  `kuantitas_dc` decimal(10,2) NOT NULL DEFAULT 0.00,
  `tarif_dc` int(10) UNSIGNED NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `tb_dry_clean`
--

INSERT INTO `tb_dry_clean` (`id_dc`, `nama_paket_dc`, `waktu_kerja_dc`, `kuantitas_dc`, `tarif_dc`) VALUES
(1, 'Dry Clean Reguler', '3 Hari', 1.00, 10000),
(2, 'Dry Clean Express', '1 Hari', 1.00, 15000);

-- --------------------------------------------------------

--
-- Table structure for table `tb_order_ck`
--

CREATE TABLE `tb_order_ck` (
  `id_order_ck` int(10) UNSIGNED NOT NULL,
  `or_ck_number` varchar(30) NOT NULL,
  `nama_pel_ck` varchar(100) NOT NULL,
  `no_telp_ck` varchar(30) NOT NULL,
  `alamat_ck` text NOT NULL,
  `jenis_paket_ck` varchar(100) NOT NULL,
  `wkt_krj_ck` varchar(50) NOT NULL,
  `berat_qty_ck` decimal(10,2) NOT NULL DEFAULT 0.00,
  `harga_perkilo` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `tgl_masuk_ck` date NOT NULL,
  `tgl_keluar_ck` date NOT NULL,
  `tot_bayar` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `keterangan_ck` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tb_order_cs`
--

CREATE TABLE `tb_order_cs` (
  `id_order_cs` int(10) UNSIGNED NOT NULL,
  `or_cs_number` varchar(30) NOT NULL,
  `nama_pel_cs` varchar(100) NOT NULL,
  `no_telp_cs` varchar(30) NOT NULL,
  `alamat_cs` text NOT NULL,
  `jenis_paket_cs` varchar(100) NOT NULL,
  `wkt_krj_cs` varchar(50) NOT NULL,
  `jml_pcs` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `harga_perpcs` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `tgl_masuk_cs` date NOT NULL,
  `tgl_keluar_cs` date NOT NULL,
  `tot_bayar` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `keterangan_cs` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tb_order_dc`
--

CREATE TABLE `tb_order_dc` (
  `id_order_dc` int(10) UNSIGNED NOT NULL,
  `or_dc_number` varchar(30) NOT NULL,
  `nama_pel_dc` varchar(100) NOT NULL,
  `no_telp_dc` varchar(30) NOT NULL,
  `alamat_dc` text NOT NULL,
  `jenis_paket_dc` varchar(100) NOT NULL,
  `wkt_krj_dc` varchar(50) NOT NULL,
  `berat_qty_dc` decimal(10,2) NOT NULL DEFAULT 0.00,
  `harga_perkilo` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `tgl_masuk_dc` date NOT NULL,
  `tgl_keluar_dc` date NOT NULL,
  `tot_bayar` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `keterangan_dc` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tb_riwayat_ck`
--

CREATE TABLE `tb_riwayat_ck` (
  `id_ck` int(10) UNSIGNED NOT NULL,
  `or_number` varchar(30) NOT NULL,
  `pelanggan` varchar(100) NOT NULL,
  `no_telp` varchar(30) NOT NULL,
  `alamat` text NOT NULL,
  `jns_paket` varchar(100) NOT NULL,
  `wkt_kerja` varchar(50) NOT NULL,
  `berat` decimal(10,2) NOT NULL DEFAULT 0.00,
  `h_perkilo` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `tgl_msk` varchar(50) NOT NULL,
  `tgl_klr` varchar(50) NOT NULL,
  `total` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `nominal_bayar` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `kembalian` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `status` varchar(20) NOT NULL DEFAULT 'Sukses',
  `keterangan` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tb_riwayat_cs`
--

CREATE TABLE `tb_riwayat_cs` (
  `id_cs` int(10) UNSIGNED NOT NULL,
  `or_number` varchar(30) NOT NULL,
  `pelanggan` varchar(100) NOT NULL,
  `no_telp` varchar(30) NOT NULL,
  `alamat` text NOT NULL,
  `jns_paket` varchar(100) NOT NULL,
  `wkt_kerja` varchar(50) NOT NULL,
  `jml_pcs` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `h_perpcs` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `tgl_msk` varchar(50) NOT NULL,
  `tgl_klr` varchar(50) NOT NULL,
  `total` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `nominal_bayar` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `kembalian` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `status` varchar(20) NOT NULL DEFAULT 'Sukses',
  `keterangan` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tb_riwayat_dc`
--

CREATE TABLE `tb_riwayat_dc` (
  `id_dc` int(10) UNSIGNED NOT NULL,
  `or_number` varchar(30) NOT NULL,
  `pelanggan` varchar(100) NOT NULL,
  `no_telp` varchar(30) NOT NULL,
  `alamat` text NOT NULL,
  `jns_paket` varchar(100) NOT NULL,
  `wkt_kerja` varchar(50) NOT NULL,
  `berat` decimal(10,2) NOT NULL DEFAULT 0.00,
  `h_perkilo` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `tgl_msk` varchar(50) NOT NULL,
  `tgl_klr` varchar(50) NOT NULL,
  `total` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `nominal_bayar` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `kembalian` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `status` varchar(20) NOT NULL DEFAULT 'Sukses',
  `keterangan` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `master`
--
ALTER TABLE `master`
  ADD PRIMARY KEY (`id_user`),
  ADD UNIQUE KEY `uk_master_email` (`email`),
  ADD UNIQUE KEY `uk_master_username` (`username`);

--
-- Indexes for table `tb_cuci_komplit`
--
ALTER TABLE `tb_cuci_komplit`
  ADD PRIMARY KEY (`id_ck`);

--
-- Indexes for table `tb_cuci_satuan`
--
ALTER TABLE `tb_cuci_satuan`
  ADD PRIMARY KEY (`id_cs`);

--
-- Indexes for table `tb_dry_clean`
--
ALTER TABLE `tb_dry_clean`
  ADD PRIMARY KEY (`id_dc`);

--
-- Indexes for table `tb_order_ck`
--
ALTER TABLE `tb_order_ck`
  ADD PRIMARY KEY (`id_order_ck`),
  ADD UNIQUE KEY `uk_tb_order_ck_number` (`or_ck_number`);

--
-- Indexes for table `tb_order_cs`
--
ALTER TABLE `tb_order_cs`
  ADD PRIMARY KEY (`id_order_cs`),
  ADD UNIQUE KEY `uk_tb_order_cs_number` (`or_cs_number`);

--
-- Indexes for table `tb_order_dc`
--
ALTER TABLE `tb_order_dc`
  ADD PRIMARY KEY (`id_order_dc`),
  ADD UNIQUE KEY `uk_tb_order_dc_number` (`or_dc_number`);

--
-- Indexes for table `tb_riwayat_ck`
--
ALTER TABLE `tb_riwayat_ck`
  ADD PRIMARY KEY (`id_ck`),
  ADD KEY `idx_riwayat_ck_order` (`or_number`);

--
-- Indexes for table `tb_riwayat_cs`
--
ALTER TABLE `tb_riwayat_cs`
  ADD PRIMARY KEY (`id_cs`),
  ADD KEY `idx_riwayat_cs_order` (`or_number`);

--
-- Indexes for table `tb_riwayat_dc`
--
ALTER TABLE `tb_riwayat_dc`
  ADD PRIMARY KEY (`id_dc`),
  ADD KEY `idx_riwayat_dc_order` (`or_number`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `master`
--
ALTER TABLE `master`
  MODIFY `id_user` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `tb_cuci_komplit`
--
ALTER TABLE `tb_cuci_komplit`
  MODIFY `id_ck` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `tb_cuci_satuan`
--
ALTER TABLE `tb_cuci_satuan`
  MODIFY `id_cs` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `tb_dry_clean`
--
ALTER TABLE `tb_dry_clean`
  MODIFY `id_dc` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `tb_order_ck`
--
ALTER TABLE `tb_order_ck`
  MODIFY `id_order_ck` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tb_order_cs`
--
ALTER TABLE `tb_order_cs`
  MODIFY `id_order_cs` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tb_order_dc`
--
ALTER TABLE `tb_order_dc`
  MODIFY `id_order_dc` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tb_riwayat_ck`
--
ALTER TABLE `tb_riwayat_ck`
  MODIFY `id_ck` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tb_riwayat_cs`
--
ALTER TABLE `tb_riwayat_cs`
  MODIFY `id_cs` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tb_riwayat_dc`
--
ALTER TABLE `tb_riwayat_dc`
  MODIFY `id_dc` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
