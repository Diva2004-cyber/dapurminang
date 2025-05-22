-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Waktu pembuatan: 22 Bulan Mei 2025 pada 14.25
-- Versi server: 10.4.32-MariaDB
-- Versi PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `restaurant_website`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `clients`
--

CREATE TABLE `clients` (
  `client_id` int(5) NOT NULL,
  `client_name` varchar(50) NOT NULL,
  `client_phone` varchar(50) NOT NULL,
  `client_email` varchar(100) NOT NULL,
  `client_city` varchar(50) DEFAULT NULL,
  `client_postal_code` varchar(10) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data untuk tabel `clients`
--

INSERT INTO `clients` (`client_id`, `client_name`, `client_phone`, `client_email`, `client_city`, `client_postal_code`) VALUES
(9, 'Clinet 1', '02020202020', 'client1@gmail.com', NULL, NULL),
(10, 'Client 10', '0638383933', 'client10@gmail.com', NULL, NULL),
(11, 'Client 11', '06242556272', 'client11@yahoo.fr', NULL, NULL),
(13, 'Client 12', '030303030202', 'client1133@gmail.com', NULL, NULL),
(14, 'Client 12', '030303030', 'client14@gmail.com', NULL, NULL),
(16, 'Client 14', '0203203203', 'client14@gmail.com', NULL, NULL),
(17, 'Client 17', '0737373822', 'client17@gmail.com', NULL, NULL),
(18, 'Client 12', '02920320', 'client12@yahoo.fr', NULL, NULL),
(19, 'Test', '1034304300', 'test@gmail.com', NULL, NULL),
(20, 'aksasdkoask', '', 'nasipadang@gmail.com', NULL, NULL),
(21, 'asdadsa', '', 'asdadas@gmail.com', NULL, NULL),
(22, 'asd', '08222222222', 'adkosakdoask@gmail.com', NULL, NULL),
(23, 'DIVA PRAYOGA', '085930211309', 'amuadnasir@gmail.com', NULL, NULL),
(24, 'Alexander The Great', '0812301283021', 'bambangpamungkaos123@gmail.com', 'Tasik', '461723'),
(25, 'babang torik', '0812301283021', 'juraganbakso123@gmail.com', 'Tasik', '461723'),
(26, 'kucing', '08222222222', '12312312@gmail.com', NULL, NULL),
(27, 'asd', '08222222222', 'bambangpamungkaos123@gmail.com', NULL, NULL),
(28, 'Mang garok', '123', 'bambangpamungkaos123@gmail.com', NULL, NULL),
(29, 'asda', '08222222222', 'bambangpamungkaos123@gmail.com', NULL, NULL),
(30, 'asd', '9089078967', 'kurama@gmail.com', NULL, NULL),
(31, 'Bagas', '085930211309', 'divapap2703@gmail.com', NULL, NULL);

-- --------------------------------------------------------

--
-- Struktur dari tabel `image_gallery`
--

CREATE TABLE `image_gallery` (
  `image_id` int(2) NOT NULL,
  `image_name` varchar(30) NOT NULL,
  `image` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data untuk tabel `image_gallery`
--

INSERT INTO `image_gallery` (`image_id`, `image_name`, `image`) VALUES
(6, 'masakchef', '6805429e32aa0.jpg'),
(7, 'Receipts', '680542a89f84a.jpg'),
(8, 'Pintu', '680542b854040.jpeg'),
(9, 'Rendang', '680542c224f30.jpg'),
(10, 'Menu', '680542c92438f.jpg');

-- --------------------------------------------------------

--
-- Struktur dari tabel `ingredients`
--

CREATE TABLE `ingredients` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `stock_quantity` int(11) NOT NULL DEFAULT 0,
  `unit` varchar(20) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `in_order`
--

CREATE TABLE `in_order` (
  `id` int(5) NOT NULL,
  `order_id` int(5) NOT NULL,
  `menu_id` int(5) NOT NULL,
  `quantity` int(3) NOT NULL DEFAULT 1,
  `notes` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data untuk tabel `in_order`
--

INSERT INTO `in_order` (`id`, `order_id`, `menu_id`, `quantity`, `notes`) VALUES
(8, 10, 16, 1, NULL),
(9, 11, 12, 1, NULL),
(10, 11, 16, 1, NULL),
(11, 12, 11, 1, NULL),
(12, 12, 12, 1, NULL),
(13, 12, 16, 1, NULL),
(14, 13, 1, 1, NULL),
(15, 13, 2, 1, NULL),
(16, 14, 1, 1, NULL),
(17, 15, 1, 1, NULL),
(18, 16, 1, 1, NULL),
(19, 17, 1, 1, NULL),
(20, 18, 1, 1, NULL),
(21, 19, 5, 1, NULL),
(22, 19, 3, 1, NULL),
(23, 19, 2, 1, NULL),
(24, 19, 24, 1, NULL),
(25, 19, 26, 5, NULL),
(26, 20, 1, 1, NULL),
(27, 20, 2, 1, NULL),
(28, 20, 6, 1, NULL),
(29, 21, 1, 1, NULL),
(30, 21, 3, 1, NULL),
(31, 21, 7, 3, NULL),
(32, 22, 2, 1, NULL),
(33, 22, 1, 1, NULL),
(34, 22, 12, 1, NULL),
(35, 23, 1, 1, NULL),
(36, 23, 2, 1, NULL),
(37, 24, 1, 1, NULL),
(38, 24, 2, 1, NULL),
(39, 25, 1, 1, NULL),
(40, 28, 1, 1, NULL),
(41, 29, 1, 1, NULL),
(42, 30, 1, 1, NULL),
(43, 32, 1, 1, NULL),
(44, 33, 2, 3, NULL),
(45, 34, 2, 4, NULL),
(46, 35, 1, 1, NULL),
(47, 36, 3, 2, NULL),
(48, 37, 5, 3, NULL),
(49, 38, 2, 3, NULL),
(50, 39, 1, 1, NULL),
(51, 41, 26, 1, ''),
(52, 41, 1, 1, ''),
(53, 41, 27, 1, ''),
(54, 42, 1, 2, ''),
(55, 43, 1, 1, ''),
(56, 44, 5, 1, ''),
(57, 44, 1, 2, ''),
(58, 45, 30, 1, ''),
(59, 46, 3, 1, ''),
(60, 47, 2, 1, ''),
(61, 48, 1, 2, NULL),
(62, 49, 16, 1, NULL),
(63, 50, 12, 1, NULL),
(64, 51, 11, 1, NULL),
(65, 51, 5, 1, NULL),
(66, 51, 17, 1, NULL);

-- --------------------------------------------------------

--
-- Struktur dari tabel `menus`
--

CREATE TABLE `menus` (
  `menu_id` int(5) NOT NULL,
  `menu_name` varchar(100) NOT NULL,
  `menu_description` varchar(255) NOT NULL,
  `menu_price` decimal(10,3) NOT NULL,
  `menu_image` varchar(255) NOT NULL,
  `category_id` int(3) NOT NULL,
  `menu_status` varchar(50) DEFAULT 'tersedia'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data untuk tabel `menus`
--

INSERT INTO `menus` (`menu_id`, `menu_name`, `menu_description`, `menu_price`, `menu_image`, `category_id`, `menu_status`) VALUES
(1, 'Rendang', 'Daging sapi dimasak perlahan dengan santan dan bumbu khas Minang hingga meresap dan bertekstur empuk.', 25.000, '85621_rendang.jpg', 1, 'tersedia'),
(2, 'Gulai Ayam', 'Ayam dimasak dalam kuah gulai berwarna kuning dengan santan dan rempah-rempah khas.', 20.000, '89733_gulai ayam.jpeg', 1, 'tersedia'),
(3, 'Ayam Pop', 'Ayam goreng khas Padang yang direbus dengan bumbu lalu digoreng tanpa kulit, disajikan dengan sambal khas.', 20.000, '22278_ayam pop.jpg', 1, 'tersedia'),
(5, 'Ikan Asam Padeh', 'Ikan dimasak dalam kuah pedas dan asam yang menyegarkan.', 25.000, '8513_ikan asam padeh.jpg', 2, 'tersedia'),
(6, 'Ikan Bakar', 'Ikan segar dibakar dengan bumbu khas, menghasilkan rasa gurih dan sedikit smoky.', 30.000, '17762_ikan bakar.jpg', 2, 'tersedia'),
(7, 'Gulai Ikan', 'Ikan dimasak dengan santan dan bumbu gulai, menciptakan rasa yang kaya dan lezat.', 28.000, '85354_gulai ikan.jpg', 2, 'tersedia'),
(9, 'Rendang', 'Daging sapi dimasak perlahan dengan santan dan bumbu khas Minang hingga meresap dan bertekstur empuk.', 25.000, '30783_rendang.jpg', 3, 'tersedia'),
(11, 'Gulai Daging', 'Daging sapi dimasak dalam kuah gulai santan yang kaya rempah dan bercita rasa gurih.', 25.000, '74938_gulai daging.jpg', 3, 'tersedia'),
(12, 'Dendeng Balado', 'Daging sapi tipis yang digoreng kering lalu diberi sambal merah pedas khas.', 27.000, '54373_dendeng balado.jpg', 3, 'tersedia'),
(16, 'Gulai Nangka', 'Nangka muda dimasak dalam kuah gulai dengan santan dan bumbu khas Padang.', 10.000, '53662_gulai nangka.jpg', 4, 'tersedia'),
(17, 'Daun Singkong Rebus', 'Daun singkong yang direbus hingga empuk dan biasanya disajikan dengan sambal.', 8.000, '35500_daun singkong rebus.jpg', 4, 'tersedia'),
(18, 'Sambalado Terong', 'Terong goreng yang disiram dengan sambal merah atau hijau khas Minang.', 10.000, '63735_sambalado terong.jpg', 4, 'tersedia'),
(19, 'Sambal Hijau', 'Sambal khas Minang yang terbuat dari cabai hijau yang ditumbuk kasar dengan bawang dan tomat hijau.', 5.000, '69660_sambal hijau.jpg', 5, 'tersedia'),
(20, 'Sambal Merah', 'Sambal pedas dengan cabai merah dan bawang yang memberikan rasa pedas gurih.', 5.000, '9799_sambal merah.jpg', 5, 'tersedia'),
(21, 'Sambal Lado Ijo', 'Varian sambal hijau dengan rasa yang lebih pedas dan aroma khas.', 5.000, '1961_sambal lado ijo.jpg', 5, 'tersedia'),
(22, 'Telur Dadar', 'Telur dadar khas Padang yang tebal, berbumbu, dan digoreng hingga renyah di bagian luar.', 10.000, '72585_telur dadar.jpg', 6, 'tersedia'),
(23, 'Perkedel Kentang', 'Kentang tumbuk yang dibumbui dan digoreng hingga berwarna keemasan.', 8.000, '85748_perkedel kentang.jpg', 6, 'tersedia'),
(24, 'Kerupuk Jangek', 'Kerupuk kulit sapi yang renyah dan gurih, cocok sebagai pelengkap.', 7.000, '12907_kerupuk jangek.jpg', 6, 'tersedia'),
(25, 'Teh Talua', 'Teh khas Minang yang dicampur dengan kuning telur, memberikan rasa lembut dan aroma khas.', 10.000, '9597_teh talua.jpg', 7, 'tersedia'),
(26, 'Es Jeruk', 'Minuman segar dari perasan jeruk asli yang manis dan asam.', 8.000, '38801_es jeruk.jpg', 7, 'tersedia'),
(27, 'Kopi Hitam', 'Kopi hitam khas Sumatra yang memiliki rasa kuat dan aroma khas.', 10.000, '98066_kopi hitam.jpg', 7, 'tersedia'),
(28, 'Paket Nasi + Ayam Pop + Es Teh', 'Kombinasi nasi putih, ayam pop, dan teh es yang menyegarkan.', 35.000, '68160_WhatsApp Image 2025-02-27 at 22.39.13 (1).jpeg', 8, 'tersedia'),
(29, 'Paket Nasi + Rendang + Es Jeruk', 'Paket favorit berisi rendang dengan nasi putih serta es jeruk sebagai minuman.', 40.000, '98052_WhatsApp Image 2025-02-27 at 22.39.13.jpeg', 8, 'tersedia'),
(30, 'Paket Nasi + Dendeng + Teh Hangat', 'Dendeng balado pedas dengan nasi dan teh hangat yang menenangkan.', 37.000, '44349_WhatsApp Image 2025-02-27 at 22.39.13 (2).jpeg', 8, 'tersedia');

-- --------------------------------------------------------

--
-- Struktur dari tabel `menu_categories`
--

CREATE TABLE `menu_categories` (
  `category_id` int(3) NOT NULL,
  `category_name` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data untuk tabel `menu_categories`
--

INSERT INTO `menu_categories` (`category_id`, `category_name`) VALUES
(1, 'menu utama'),
(2, 'menu ikan'),
(3, 'menu daging'),
(4, 'menu sayur'),
(5, 'menu sambal'),
(6, 'menu tambahan'),
(7, 'minuman'),
(8, 'paket hemat');

-- --------------------------------------------------------

--
-- Struktur dari tabel `menu_ingredients`
--

CREATE TABLE `menu_ingredients` (
  `menu_id` int(11) NOT NULL,
  `ingredient_id` int(11) NOT NULL,
  `quantity_used` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `message` text NOT NULL,
  `status` varchar(20) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `placed_orders`
--

CREATE TABLE `placed_orders` (
  `order_id` int(5) NOT NULL,
  `order_time` datetime NOT NULL,
  `client_id` int(5) NOT NULL,
  `reservation_id` int(5) DEFAULT NULL,
  `order_type` enum('delivery','dine_in') NOT NULL DEFAULT 'delivery',
  `delivery_address` varchar(255) NOT NULL,
  `delivered` tinyint(1) NOT NULL DEFAULT 0,
  `canceled` tinyint(1) NOT NULL DEFAULT 0,
  `cancellation_reason` varchar(255) DEFAULT NULL,
  `payment_token` varchar(255) DEFAULT NULL,
  `payment_method` varchar(50) DEFAULT NULL,
  `payment_status` enum('pending','menunggu-verifikasi','sukses','ditolak') DEFAULT 'pending',
  `payment_proof` varchar(255) DEFAULT NULL,
  `payment_notes` text DEFAULT NULL,
  `total_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `shipping_cost` decimal(10,2) NOT NULL DEFAULT 0.00,
  `shipping_status` enum('pending','calculated','confirmed') NOT NULL DEFAULT 'pending',
  `shipping_notes` text DEFAULT NULL,
  `order_status` enum('Menunggu','Sedang Diproses','Terkirim','Dibatalkan') DEFAULT 'Menunggu',
  `city` varchar(50) DEFAULT NULL,
  `postal_code` varchar(10) DEFAULT NULL,
  `order_notes` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data untuk tabel `placed_orders`
--

INSERT INTO `placed_orders` (`order_id`, `order_time`, `client_id`, `reservation_id`, `order_type`, `delivery_address`, `delivered`, `canceled`, `cancellation_reason`, `payment_token`, `payment_method`, `payment_status`, `payment_proof`, `payment_notes`, `total_amount`, `shipping_cost`, `shipping_status`, `shipping_notes`, `order_status`, `city`, `postal_code`, `order_notes`) VALUES
(10, '2023-07-01 04:02:00', 16, NULL, 'delivery', 'Bloc A', 1, 0, NULL, NULL, NULL, 'pending', NULL, NULL, 0.00, 0.00, 'pending', NULL, '', NULL, NULL, NULL),
(11, '2023-10-30 20:09:00', 18, NULL, 'delivery', 'Test testst asds', 0, 0, NULL, NULL, NULL, 'pending', NULL, NULL, 0.00, 0.00, 'pending', NULL, '', NULL, NULL, NULL),
(12, '2023-10-30 21:46:00', 19, NULL, 'delivery', 'tests sd', 0, 0, NULL, NULL, NULL, 'pending', NULL, NULL, 0.00, 0.00, 'pending', NULL, '', NULL, NULL, NULL),
(13, '2025-04-29 18:36:00', 22, NULL, 'delivery', 'asda', 0, 0, NULL, NULL, NULL, 'pending', NULL, NULL, 0.00, 0.00, 'pending', NULL, '', NULL, NULL, NULL),
(14, '2025-04-29 19:24:00', 23, NULL, 'delivery', 'DUSUN KULON RT 12 RW 04 DESA CIMARI KEC CIKONENG', 0, 0, NULL, NULL, NULL, 'pending', NULL, NULL, 0.00, 0.00, 'pending', NULL, '', NULL, NULL, NULL),
(15, '2025-04-29 19:25:00', 23, NULL, 'delivery', 'DUSUN KULON RT 12 RW 04 DESA CIMARI KEC CIKONENG', 0, 0, NULL, NULL, NULL, 'pending', NULL, NULL, 0.00, 0.00, 'pending', NULL, 'Terkirim', NULL, NULL, NULL),
(16, '2025-05-01 15:52:00', 23, NULL, 'delivery', 'DUSUN KULON RT 12 RW 04 DESA CIMARI KEC CIKONENG', 0, 0, NULL, NULL, 'Bank Transfer', 'pending', NULL, NULL, 0.00, 0.00, 'pending', NULL, 'Dibatalkan', NULL, NULL, NULL),
(17, '2025-05-01 16:42:00', 23, NULL, 'delivery', 'DUSUN KULON RT 12 RW 04 DESA CIMARI KEC CIKONENG', 0, 0, NULL, NULL, 'Cash on Delivery', 'pending', NULL, NULL, 0.00, 0.00, 'pending', NULL, 'Sedang Diproses', NULL, NULL, NULL),
(18, '2025-05-01 16:46:00', 23, NULL, 'delivery', 'DUSUN KULON RT 12 RW 04 DESA CIMARI KEC CIKONENG', 0, 0, NULL, NULL, 'Cash on Delivery', 'pending', NULL, NULL, 0.00, 0.00, 'pending', NULL, 'Sedang Diproses', NULL, NULL, NULL),
(19, '2025-05-01 17:19:00', 23, NULL, 'delivery', 'DUSUN KULON RT 12 RW 04 DESA CIMARI KEC CIKONENG', 0, 0, NULL, NULL, 'GoPay', 'sukses', 'uploads/payment_proof/bukti_19_1746113004_tengkorak.jpeg', '', 0.00, 0.00, 'pending', NULL, 'Sedang Diproses', NULL, NULL, NULL),
(20, '2025-05-02 09:18:00', 24, NULL, 'delivery', '123', 0, 0, NULL, NULL, 'Cash on Delivery', 'pending', NULL, NULL, 0.00, 0.00, 'pending', NULL, 'Terkirim', NULL, NULL, NULL),
(21, '2025-05-02 09:36:00', 24, NULL, 'delivery', 'Jalan,. Jalan', 0, 0, NULL, NULL, 'GoPay', 'pending', NULL, NULL, 0.00, 0.00, 'pending', NULL, 'Menunggu', NULL, NULL, NULL),
(22, '2025-05-02 09:40:00', 24, NULL, 'delivery', 'Jalan,. Jalan', 0, 0, NULL, NULL, 'Cash on Delivery', 'pending', NULL, NULL, 0.00, 0.00, 'pending', NULL, 'Terkirim', 'Tasik', '461723', NULL),
(23, '2025-05-02 09:40:00', 24, NULL, 'delivery', 'Jalan,. Jalan', 0, 0, NULL, NULL, 'Cash on Delivery', 'pending', NULL, NULL, 0.00, 0.00, 'pending', NULL, 'Menunggu', 'Tasik', '461723', NULL),
(24, '2025-05-02 21:19:00', 25, NULL, 'delivery', '123123123', 0, 0, NULL, NULL, 'Cash on Delivery', 'pending', NULL, NULL, 0.00, 0.00, 'pending', NULL, 'Menunggu', NULL, NULL, NULL),
(25, '2025-05-02 21:21:00', 25, NULL, 'delivery', '123123123', 0, 0, NULL, NULL, 'Cash on Delivery', 'pending', NULL, NULL, 0.00, 0.00, 'pending', NULL, 'Menunggu', NULL, NULL, NULL),
(28, '2025-05-02 21:37:00', 25, NULL, 'delivery', '123123123', 0, 0, NULL, NULL, 'Cash on Delivery', 'pending', NULL, NULL, 0.00, 0.00, 'pending', NULL, 'Menunggu', 'Tasik', '', NULL),
(29, '2025-05-02 21:38:00', 25, NULL, 'delivery', '123123123', 0, 0, NULL, NULL, 'Cash on Delivery', 'pending', NULL, NULL, 0.00, 0.00, 'pending', NULL, 'Menunggu', 'Tasik', '461723', NULL),
(30, '2025-05-02 21:44:00', 25, NULL, 'delivery', '123123123', 0, 0, NULL, NULL, 'Cash on Delivery', 'pending', NULL, NULL, 0.00, 0.00, 'pending', NULL, 'Menunggu', 'Tasik', '461723', NULL),
(32, '2025-05-03 14:29:00', 23, NULL, 'delivery', 'DUSUN KULON RT 12 RW 04 DESA CIMARI KEC CIKONENG', 0, 0, NULL, NULL, 'Cash on Delivery', 'pending', NULL, NULL, 6000.00, 6000.00, 'calculated', '', 'Menunggu', NULL, NULL, NULL),
(33, '2025-05-03 14:53:00', 23, NULL, 'delivery', 'DUSUN KULON RT 12 RW 04 DESA CIMARI KEC CIKONENG', 0, 0, NULL, NULL, 'Cash on Delivery', 'pending', NULL, NULL, 10000.00, 10000.00, 'calculated', '', 'Menunggu', NULL, NULL, NULL),
(34, '2025-05-03 15:15:00', 23, NULL, 'delivery', 'DUSUN KULON RT 12 RW 04 DESA CIMARI KEC CIKONENG', 0, 0, NULL, NULL, 'Cash on Delivery', 'pending', NULL, NULL, 0.00, 0.00, 'pending', NULL, 'Menunggu', NULL, NULL, NULL),
(35, '2025-05-03 15:20:00', 23, NULL, 'delivery', 'DUSUN KULON RT 12 RW 04 DESA CIMARI KEC CIKONENG', 0, 0, NULL, NULL, 'Cash on Delivery', 'pending', NULL, NULL, 33000.00, 33000.00, 'calculated', '', 'Menunggu', NULL, NULL, NULL),
(36, '2025-05-03 15:25:00', 23, NULL, 'delivery', 'DUSUN KULON RT 12 RW 04 DESA CIMARI KEC CIKONENG', 0, 0, NULL, NULL, 'Cash on Delivery', 'pending', NULL, NULL, 0.00, 0.00, 'pending', NULL, 'Menunggu', NULL, NULL, NULL),
(37, '2025-05-03 15:37:00', 23, NULL, 'delivery', 'DUSUN KULON RT 12 RW 04 DESA CIMARI KEC CIKONENG', 0, 0, NULL, NULL, 'Cash on Delivery', 'pending', NULL, NULL, 28000.00, 28000.00, 'calculated', '', 'Menunggu', NULL, NULL, NULL),
(38, '2025-05-03 16:12:00', 23, NULL, 'delivery', 'DUSUN KULON RT 12 RW 04 DESA CIMARI KEC CIKONENG', 0, 0, NULL, NULL, 'Cash on Delivery', 'pending', NULL, NULL, 18000.00, 18000.00, 'calculated', '', 'Menunggu', NULL, NULL, NULL),
(39, '2025-05-03 17:39:00', 25, NULL, 'delivery', 'asdasd', 0, 0, NULL, NULL, 'Cash on Delivery', 'pending', NULL, NULL, 28500.00, 28500.00, 'calculated', '', 'Menunggu', NULL, NULL, NULL),
(41, '2025-05-09 01:44:00', 23, NULL, 'delivery', 'DUSUN KULON RT 12 RW 04 DESA CIMARI KEC CIKONENG', 0, 0, NULL, NULL, 'Bank Transfer', 'pending', NULL, NULL, 43.00, 0.00, 'pending', NULL, 'Menunggu', NULL, NULL, NULL),
(42, '2025-05-09 01:44:00', 23, NULL, 'delivery', 'DUSUN KULON RT 12 RW 04 DESA CIMARI KEC CIKONENG', 0, 0, NULL, NULL, 'Cash on Delivery', 'pending', NULL, NULL, 50.00, 0.00, 'pending', NULL, 'Menunggu', NULL, NULL, NULL),
(43, '2025-05-09 01:48:00', 23, NULL, 'delivery', 'DUSUN KULON RT 12 RW 04 DESA CIMARI KEC CIKONENG', 0, 0, NULL, NULL, 'Cash on Delivery', 'pending', NULL, NULL, 12025.00, 12000.00, 'calculated', '', 'Menunggu', NULL, NULL, NULL),
(44, '2025-05-09 01:51:00', 23, NULL, 'delivery', 'DUSUN KULON RT 12 RW 04 DESA CIMARI KEC CIKONENG', 0, 0, NULL, NULL, 'GoPay', 'sukses', 'uploads/payment_proofs/payment_44_20250509015626.jpg', '', 75.00, 0.00, 'calculated', '', 'Sedang Diproses', NULL, NULL, NULL),
(45, '2025-05-09 02:01:00', 23, NULL, 'delivery', 'DUSUN KULON RT 12 RW 04 DESA CIMARI KEC CIKONENG', 0, 0, NULL, NULL, 'Cash on Delivery', 'pending', NULL, NULL, 18037.00, 18000.00, 'calculated', '', 'Menunggu', NULL, NULL, NULL),
(46, '2025-05-09 02:25:00', 23, NULL, 'delivery', 'DUSUN KULON RT 12 RW 04 DESA CIMARI KEC CIKONENG', 0, 0, NULL, NULL, 'Cash on Delivery', 'pending', NULL, NULL, 20.00, 0.00, 'pending', NULL, 'Menunggu', NULL, NULL, NULL),
(47, '2025-05-10 01:08:00', 23, NULL, 'delivery', 'DUSUN KULON RT 12 RW 04 DESA CIMARI KEC CIKONENG', 0, 0, NULL, NULL, 'GoPay', 'menunggu-verifikasi', 'uploads/payment_proofs/payment_47_20250510010835.jpg', NULL, 20.00, 0.00, 'calculated', '', 'Menunggu', NULL, NULL, NULL),
(48, '2025-05-10 01:28:00', 31, NULL, 'delivery', 'DUSUN KULON RT 12 RW 04 DESA CIMARI KEC CIKONENG', 0, 0, NULL, NULL, 'Cash on Delivery', 'pending', NULL, NULL, 0.00, 0.00, 'pending', NULL, 'Menunggu', NULL, NULL, NULL),
(49, '2025-05-10 01:36:00', 31, NULL, 'delivery', 'DUSUN KULON RT 12 RW 04 DESA CIMARI KEC CIKONENG', 0, 0, 'Dibatalkan oleh pelanggan karena ongkir tidak sesuai', NULL, 'Cash on Delivery', 'pending', NULL, NULL, 28000.00, 18000.00, 'calculated', '', 'Dibatalkan', NULL, NULL, ''),
(50, '2025-05-10 01:45:00', 31, NULL, 'delivery', 'DUSUN KULON RT 12 RW 04 DESA CIMARI KEC CIKONENG', 0, 0, NULL, NULL, 'Cash on Delivery', 'pending', NULL, NULL, 33600.00, 6600.00, 'calculated', '', 'Terkirim', NULL, NULL, ''),
(51, '2025-05-10 01:50:00', 31, NULL, 'delivery', 'DUSUN KULON RT 12 RW 04 DESA CIMARI KEC CIKONENG', 0, 0, NULL, NULL, 'GoPay', 'pending', NULL, NULL, 58000.00, 0.00, 'pending', NULL, 'Menunggu', NULL, NULL, '');

-- --------------------------------------------------------

--
-- Struktur dari tabel `reservations`
--

CREATE TABLE `reservations` (
  `reservation_id` int(5) NOT NULL,
  `date_created` datetime NOT NULL,
  `client_id` int(5) NOT NULL,
  `client_name` varchar(100) DEFAULT NULL,
  `client_email` varchar(100) DEFAULT NULL,
  `client_phone` varchar(20) DEFAULT NULL,
  `selected_time` datetime NOT NULL,
  `end_time` datetime NOT NULL,
  `nbr_guests` int(2) NOT NULL,
  `table_id` int(3) NOT NULL,
  `special_requests` text DEFAULT NULL,
  `cancellation_reason` varchar(255) DEFAULT NULL,
  `payment_status` enum('pending','paid','cancelled') NOT NULL DEFAULT 'pending',
  `payment_method` enum('bank_transfer','cash') DEFAULT NULL,
  `payment_amount` decimal(10,2) DEFAULT NULL,
  `payment_proof` varchar(255) DEFAULT NULL,
  `payment_date` datetime DEFAULT NULL,
  `transaction_id` varchar(100) DEFAULT NULL,
  `status` enum('pending','confirmed','completed','cancelled') DEFAULT 'pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data untuk tabel `reservations`
--

INSERT INTO `reservations` (`reservation_id`, `date_created`, `client_id`, `client_name`, `client_email`, `client_phone`, `selected_time`, `end_time`, `nbr_guests`, `table_id`, `special_requests`, `cancellation_reason`, `payment_status`, `payment_method`, `payment_amount`, `payment_proof`, `payment_date`, `transaction_id`, `status`) VALUES
(78, '2025-05-03 18:27:00', 25, 'babang torik', 'juraganbakso123@gmail.com', '0812301283021', '2025-05-04 18:27:00', '2025-05-04 20:27:00', 1, 2, '', NULL, 'pending', NULL, NULL, NULL, NULL, NULL, 'pending'),
(79, '0000-00-00 00:00:00', 11, 'babang torik', 'juraganbakso123@gmail.com', '0812301283021', '2025-05-04 18:38:00', '2025-05-04 20:38:00', 1, 44, '', NULL, 'pending', NULL, NULL, NULL, NULL, NULL, 'pending'),
(80, '0000-00-00 00:00:00', 11, 'babang torik', 'juraganbakso123@gmail.com', '0812301283021', '2025-05-04 18:46:00', '2025-05-04 20:46:00', 1, 37, '', NULL, 'pending', NULL, NULL, NULL, NULL, NULL, 'pending'),
(81, '2025-05-03 18:59:00', 25, 'babang torik', 'juraganbakso123@gmail.com', '0812301283021', '2025-05-04 08:01:00', '2025-05-04 10:01:00', 1, 3, '', NULL, 'pending', NULL, NULL, NULL, NULL, NULL, 'pending'),
(82, '2025-05-03 19:51:00', 25, 'babang torik', 'juraganbakso123@gmail.com', '0812301283021', '2025-05-04 11:30:00', '2025-05-04 13:30:00', 1, 6, '', NULL, 'pending', NULL, NULL, NULL, NULL, NULL, 'pending'),
(83, '2025-05-03 20:01:00', 25, 'babang torik', 'juraganbakso123@gmail.com', '0812301283021', '2025-05-04 20:01:00', '2025-05-04 22:01:00', 1, 6, '', NULL, 'pending', NULL, NULL, NULL, NULL, NULL, 'pending'),
(84, '2025-05-03 20:09:00', 25, 'babang torik', 'juraganbakso123@gmail.com', '0812301283021', '2025-05-04 21:32:00', '2025-05-04 23:32:00', 3, 3, '', NULL, 'pending', NULL, NULL, NULL, NULL, NULL, 'pending'),
(85, '2025-05-09 01:01:00', 23, 'DIVA PRAYOGA', 'amuadnasir@gmail.com', '085930211309', '2025-05-10 01:00:00', '2025-05-10 03:00:00', 3, 4, 'minum segar', NULL, 'pending', NULL, NULL, NULL, NULL, NULL, 'pending'),
(86, '2025-05-09 01:05:00', 23, 'DIVA PRAYOGA', 'amuadnasir@gmail.com', '085930211309', '2025-05-10 01:05:00', '2025-05-10 03:05:00', 3, 5, '', NULL, 'pending', NULL, NULL, NULL, NULL, NULL, 'pending'),
(87, '2025-05-09 01:20:00', 23, 'DIVA PRAYOGA', 'amuadnasir@gmail.com', '085930211309', '2025-05-10 01:20:00', '2025-05-10 03:20:00', 1, 7, '', NULL, 'pending', NULL, NULL, NULL, NULL, NULL, 'pending'),
(88, '2025-05-09 02:15:00', 23, 'DIVA PRAYOGA', 'amuadnasir@gmail.com', '085930211309', '2025-05-10 02:15:00', '2025-05-10 04:15:00', 3, 8, '', NULL, 'pending', NULL, NULL, NULL, NULL, NULL, 'pending'),
(89, '2025-05-09 02:18:00', 23, 'DIVA PRAYOGA', 'amuadnasir@gmail.com', '085930211309', '2025-05-10 02:17:00', '2025-05-10 04:17:00', 2, 41, '', NULL, 'pending', NULL, NULL, NULL, NULL, NULL, 'pending'),
(90, '2025-05-09 02:25:00', 23, 'DIVA PRAYOGA', 'amuadnasir@gmail.com', '085930211309', '2025-05-10 02:24:00', '2025-05-10 04:24:00', 3, 1, '', NULL, 'pending', NULL, NULL, NULL, NULL, NULL, 'pending'),
(91, '2025-05-09 02:31:00', 23, 'DIVA PRAYOGA', 'amuadnasir@gmail.com', '085930211309', '2025-05-18 04:30:00', '2025-05-18 06:30:00', 4, 1, '', NULL, 'pending', NULL, NULL, NULL, NULL, NULL, 'pending'),
(92, '2025-05-09 02:31:00', 23, 'DIVA PRAYOGA', 'amuadnasir@gmail.com', '085930211309', '2025-05-10 02:31:00', '2025-05-10 04:31:00', 1, 3, '', NULL, 'pending', NULL, NULL, NULL, NULL, NULL, 'pending'),
(93, '2025-05-09 12:07:00', 23, 'DIVA PRAYOGA', 'amuadnasir@gmail.com', '085930211309', '2025-05-10 12:07:00', '2025-05-10 14:07:00', 1, 1, '', NULL, 'pending', NULL, NULL, NULL, NULL, NULL, 'pending'),
(94, '2025-05-09 12:07:00', 23, 'DIVA PRAYOGA', 'amuadnasir@gmail.com', '085930211309', '2025-05-10 12:07:00', '2025-05-10 14:07:00', 1, 2, '', NULL, 'pending', NULL, NULL, NULL, NULL, NULL, 'pending'),
(95, '2025-05-09 12:07:00', 23, 'DIVA PRAYOGA', 'amuadnasir@gmail.com', '085930211309', '2025-05-10 12:07:00', '2025-05-10 14:07:00', 1, 3, '', NULL, 'pending', NULL, NULL, NULL, NULL, NULL, 'pending'),
(96, '2025-05-09 12:07:00', 23, 'DIVA PRAYOGA', 'amuadnasir@gmail.com', '085930211309', '2025-05-10 12:07:00', '2025-05-10 14:07:00', 1, 4, '', NULL, 'pending', NULL, NULL, NULL, NULL, NULL, 'pending'),
(97, '2025-05-09 12:12:00', 23, 'DIVA PRAYOGA', 'amuadnasir@gmail.com', '085930211309', '2025-05-11 13:10:00', '2025-05-11 15:10:00', 4, 6, '', NULL, 'pending', NULL, NULL, NULL, NULL, NULL, 'cancelled'),
(98, '2025-05-10 01:01:00', 23, 'DIVA PRAYOGA', 'amuadnasir@gmail.com', '085930211309', '2025-05-12 12:12:00', '2025-05-12 14:12:00', 3, 1, '', NULL, 'pending', NULL, NULL, NULL, NULL, NULL, 'pending'),
(99, '2025-05-10 01:06:00', 23, 'DIVA PRAYOGA', 'amuadnasir@gmail.com', '085930211309', '2025-05-11 01:06:00', '2025-05-11 03:06:00', 1, 4, '', NULL, 'pending', NULL, NULL, NULL, NULL, NULL, 'pending'),
(100, '2025-05-10 01:06:00', 23, 'DIVA PRAYOGA', 'amuadnasir@gmail.com', '085930211309', '2025-05-11 01:06:00', '2025-05-11 03:06:00', 2, 3, '', NULL, 'pending', NULL, NULL, NULL, NULL, NULL, 'pending'),
(101, '2025-05-10 01:09:00', 23, 'DIVA PRAYOGA', 'amuadnasir@gmail.com', '085930211309', '2025-05-11 01:07:00', '2025-05-11 03:07:00', 1, 5, '', NULL, 'pending', NULL, NULL, NULL, NULL, NULL, 'pending'),
(102, '2025-05-10 01:09:00', 23, 'DIVA PRAYOGA', 'amuadnasir@gmail.com', '085930211309', '2025-05-11 01:09:00', '2025-05-11 03:09:00', 1, 6, '', NULL, 'pending', NULL, NULL, NULL, NULL, NULL, 'pending'),
(103, '2025-05-10 01:09:00', 23, 'DIVA PRAYOGA', 'amuadnasir@gmail.com', '085930211309', '2025-05-11 01:09:00', '2025-05-11 03:09:00', 3, 9, '', NULL, 'pending', NULL, NULL, NULL, NULL, NULL, 'pending'),
(104, '2025-05-10 01:11:00', 23, 'DIVA PRAYOGA', 'amuadnasir@gmail.com', '085930211309', '2025-05-11 01:11:00', '2025-05-11 03:11:00', 1, 2, '', NULL, 'pending', NULL, NULL, NULL, NULL, NULL, 'pending'),
(105, '2025-05-10 01:17:00', 23, 'DIVA PRAYOGA', 'amuadnasir@gmail.com', '085930211309', '2025-05-11 01:17:00', '2025-05-11 03:17:00', 1, 7, '', NULL, 'pending', NULL, NULL, NULL, NULL, NULL, 'pending'),
(106, '2025-05-10 01:20:00', 23, 'DIVA PRAYOGA', 'amuadnasir@gmail.com', '085930211309', '2025-05-11 01:20:00', '2025-05-11 03:20:00', 1, 1, '', NULL, 'pending', NULL, NULL, NULL, NULL, NULL, 'pending'),
(107, '2025-05-10 01:23:00', 23, 'DIVA PRAYOGA', 'amuadnasir@gmail.com', '085930211309', '2025-05-11 01:23:00', '2025-05-11 03:23:00', 1, 8, '', NULL, 'pending', NULL, NULL, NULL, NULL, NULL, 'pending'),
(108, '2025-05-10 01:44:00', 31, 'Bagas', 'divapap2703@gmail.com', '085930211309', '2025-05-11 01:43:00', '2025-05-11 03:43:00', 1, 17, '', NULL, 'pending', NULL, NULL, NULL, NULL, NULL, 'pending'),
(109, '2025-05-10 13:24:00', 31, 'Bagas Pranam', 'divapap2703@gmail.com', '089345231876', '2025-05-11 13:21:00', '2025-05-11 15:21:00', 1, 12, '', NULL, 'pending', NULL, NULL, NULL, NULL, NULL, 'pending');

-- --------------------------------------------------------

--
-- Struktur dari tabel `reviews`
--

CREATE TABLE `reviews` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `menu_id` int(11) NOT NULL,
  `rating` int(11) DEFAULT NULL CHECK (`rating` between 1 and 5),
  `comment` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `shipping_surcharges`
--

CREATE TABLE `shipping_surcharges` (
  `surcharge_id` int(11) NOT NULL,
  `surcharge_type` enum('rush_hour','bad_weather','difficult_area','heavy_load') NOT NULL,
  `surcharge_amount` decimal(10,2) NOT NULL,
  `start_time` time DEFAULT NULL,
  `end_time` time DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `shipping_surcharges`
--

INSERT INTO `shipping_surcharges` (`surcharge_id`, `surcharge_type`, `surcharge_amount`, `start_time`, `end_time`, `is_active`) VALUES
(1, 'rush_hour', 3000.00, '07:00:00', '09:00:00', 1),
(2, 'rush_hour', 3000.00, '16:00:00', '18:00:00', 1),
(3, 'bad_weather', 5000.00, NULL, NULL, 1),
(4, 'difficult_area', 3000.00, NULL, NULL, 1),
(5, 'heavy_load', 2000.00, NULL, NULL, 1);

-- --------------------------------------------------------

--
-- Struktur dari tabel `shipping_zones`
--

CREATE TABLE `shipping_zones` (
  `zone_id` int(11) NOT NULL,
  `zone_name` varchar(100) NOT NULL,
  `min_distance` decimal(10,2) NOT NULL,
  `max_distance` decimal(10,2) NOT NULL,
  `base_cost` decimal(10,2) NOT NULL,
  `cost_per_km` decimal(10,2) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `shipping_zones`
--

INSERT INTO `shipping_zones` (`zone_id`, `zone_name`, `min_distance`, `max_distance`, `base_cost`, `cost_per_km`, `is_active`) VALUES
(1, 'Zone 1 (0-3 km)', 0.00, 3.00, 6000.00, 2000.00, 1),
(2, 'Zone 2 (3-8 km)', 3.01, 8.00, 8000.00, 2500.00, 1),
(3, 'Zone 3 (8-15 km)', 8.01, 15.00, 10000.00, 3000.00, 1);

-- --------------------------------------------------------

--
-- Struktur dari tabel `tables`
--

CREATE TABLE `tables` (
  `table_id` int(11) NOT NULL,
  `table_number` int(11) NOT NULL,
  `capacity` int(11) NOT NULL DEFAULT 4
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `users`
--

CREATE TABLE `users` (
  `user_id` int(2) NOT NULL,
  `username` varchar(20) NOT NULL,
  `email` varchar(30) NOT NULL,
  `full_name` varchar(50) NOT NULL,
  `phone_number` varchar(20) DEFAULT NULL,
  `password` varchar(100) NOT NULL,
  `alamat` varchar(255) DEFAULT NULL,
  `kota` varchar(20) DEFAULT NULL,
  `kode_pos` varchar(10) DEFAULT NULL,
  `profile_photo` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data untuk tabel `users`
--

INSERT INTO `users` (`user_id`, `username`, `email`, `full_name`, `phone_number`, `password`, `alamat`, `kota`, `kode_pos`, `profile_photo`) VALUES
(1, 'admin_user', 'user_admin@gmail.com', 'User Admin', NULL, 'f7c3bc1d808e04732adf679965ccc34ca7ae3441', NULL, NULL, NULL, NULL),
(2, 'nasipadang', 'nasipadang@gmail.com', 'aksasdkoask', NULL, '1cb22228ae39dd54c191e8d66c55e38f9fb6741c', NULL, NULL, NULL, NULL),
(3, 'asdas', 'asdadas@gmail.com', 'asdadsa', NULL, 'c7350725610ef9ce1e9e0f8c722225e1c06dc455', NULL, NULL, NULL, NULL),
(4, 'dapur', 'amuadnasir@gmail.com', 'DIVA PRAYOGA', '085930211309', '$2y$10$jdSXXUpLrvwwn9P.EJzeY.ivknTkJRmN8ou4FeBJrFLXPs4tEg4f.', 'DUSUN KULON RT 12 RW 04 DESA CIMARI KEC CIKONENG', 'Kab. Ciamis', '46261', 'uploads/profile_photos/profile_4_1746108453.jpeg'),
(5, 'maulana', '123123@gmail.com', '', NULL, '$2y$10$.iqJAJBTBnL8jGppvyVX9uyBc4.R/WzhyGNSvhu3g8zcEfpgfcb.G', NULL, NULL, NULL, NULL),
(6, 'bambang', 'bambang@gmail.com', '', NULL, '$2y$10$kg7Wj9Kqmo02zx.eAVqv/ueifjnZgYpyPLDOOxsj5Hr/EdCcxmXsO', NULL, NULL, NULL, NULL),
(7, 'bambang123', 'bambang123@gmail.com', '', NULL, '$2y$10$r7S4vUnEV/XDGJ6hLsCgr.ix.cjM7nvZBR8zIYov6mN99VoZO3W16', NULL, NULL, NULL, NULL),
(8, 'kuromo', 'kuromo123@gmail.com', '', NULL, '$2y$10$lbyVmUuASKbnCwJQrI209uF7GS6T6iadjurrI9iNhEsc2QYZaxfdK', NULL, NULL, NULL, NULL),
(9, 'test123', 'test123@gmail.com', '', NULL, '$2y$10$3b9ecnxIuwzW4DqiV1D39ep4RDtrMEuD5mVbx2rXm/z0s6aMYWIyS', NULL, NULL, NULL, NULL),
(10, 'bambangpamungkaos123', 'bambangpamungkaos123@gmail.com', 'Alexander The Great', '0812301283021', '$2y$10$IpasI/KArgp1S8CvsP2a0uetlCCNRI3xvDuZ39KkmbBdmKHDitk7W', 'Jalan,. Jalan', 'Tasik', '461723', NULL),
(11, 'juraganbakso', 'juraganbakso123@gmail.com', 'babang torik', '0812301283021', '$2y$10$eAf7dl1iME.sJcjB7NjJEOHYfg0n7kPr2C8IxjJlIbLHPJcluxlXG', '123123123', 'Tasik', '461723', 'uploads/profile_photos/profile_11_1746214266.jpeg'),
(12, 'kurama', 'kurama@gmail.com', '', NULL, '$2y$10$wIE0/IXk44e71yEF2FBnOeYDISV/8PBcPWIxgM0ApMQKiko3K0ttC', NULL, NULL, NULL, NULL),
(13, 'Bagas', 'divapap2703@gmail.com', 'Bagas Pranam', '089345231876', '$2y$10$LHPRD0Y12mU/fZCKWtVKzuhxM7a6UPlSaeWOazD3ucjZkro4JfHNe', 'tasik', 'tasik', '46333', 'uploads/profile_photos/profile_13_1746834568.jpeg'),
(14, 'hallo', 'buy@gmail.com', '', NULL, '$2y$10$QzU1jAnw3GK3Lf.KJ25PAegOoemgg0TLaijpBD55bFkUndzfumDGi', NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Struktur dari tabel `website_settings`
--

CREATE TABLE `website_settings` (
  `option_id` int(5) NOT NULL,
  `option_name` varchar(255) NOT NULL,
  `option_value` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data untuk tabel `website_settings`
--

INSERT INTO `website_settings` (`option_id`, `option_name`, `option_value`) VALUES
(1, 'restaurant_name', 'DAPOER MINANG'),
(2, 'restaurant_email', 'dapoerminang@gmail.com'),
(3, 'admin_email', 'dapoerminang@gmail.com'),
(4, 'restaurant_phonenumber', '08555777882312'),
(5, 'restaurant_address', 'Jl. Raya Tasikmalaya No. 1580, Tasikmalaya, Jawa Barat - Indonesia');

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `clients`
--
ALTER TABLE `clients`
  ADD PRIMARY KEY (`client_id`);

--
-- Indeks untuk tabel `image_gallery`
--
ALTER TABLE `image_gallery`
  ADD PRIMARY KEY (`image_id`);

--
-- Indeks untuk tabel `ingredients`
--
ALTER TABLE `ingredients`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `in_order`
--
ALTER TABLE `in_order`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_menu` (`menu_id`),
  ADD KEY `fk_order` (`order_id`);

--
-- Indeks untuk tabel `menus`
--
ALTER TABLE `menus`
  ADD PRIMARY KEY (`menu_id`),
  ADD KEY `FK_menu_category_id` (`category_id`);

--
-- Indeks untuk tabel `menu_categories`
--
ALTER TABLE `menu_categories`
  ADD PRIMARY KEY (`category_id`);

--
-- Indeks untuk tabel `menu_ingredients`
--
ALTER TABLE `menu_ingredients`
  ADD PRIMARY KEY (`menu_id`,`ingredient_id`),
  ADD KEY `ingredient_id` (`ingredient_id`);

--
-- Indeks untuk tabel `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `order_id` (`order_id`);

--
-- Indeks untuk tabel `placed_orders`
--
ALTER TABLE `placed_orders`
  ADD PRIMARY KEY (`order_id`),
  ADD KEY `fk_client` (`client_id`),
  ADD KEY `fk_reservation` (`reservation_id`);

--
-- Indeks untuk tabel `reservations`
--
ALTER TABLE `reservations`
  ADD PRIMARY KEY (`reservation_id`),
  ADD KEY `fk_reservation_client` (`client_id`),
  ADD KEY `idx_payment_status` (`payment_status`),
  ADD KEY `idx_transaction_id` (`transaction_id`),
  ADD KEY `idx_table_time` (`table_id`,`selected_time`,`end_time`);

--
-- Indeks untuk tabel `reviews`
--
ALTER TABLE `reviews`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `menu_id` (`menu_id`);

--
-- Indeks untuk tabel `shipping_surcharges`
--
ALTER TABLE `shipping_surcharges`
  ADD PRIMARY KEY (`surcharge_id`);

--
-- Indeks untuk tabel `shipping_zones`
--
ALTER TABLE `shipping_zones`
  ADD PRIMARY KEY (`zone_id`);

--
-- Indeks untuk tabel `tables`
--
ALTER TABLE `tables`
  ADD PRIMARY KEY (`table_id`),
  ADD UNIQUE KEY `table_number` (`table_number`);

--
-- Indeks untuk tabel `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indeks untuk tabel `website_settings`
--
ALTER TABLE `website_settings`
  ADD PRIMARY KEY (`option_id`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `clients`
--
ALTER TABLE `clients`
  MODIFY `client_id` int(5) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

--
-- AUTO_INCREMENT untuk tabel `image_gallery`
--
ALTER TABLE `image_gallery`
  MODIFY `image_id` int(2) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT untuk tabel `ingredients`
--
ALTER TABLE `ingredients`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `in_order`
--
ALTER TABLE `in_order`
  MODIFY `id` int(5) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=67;

--
-- AUTO_INCREMENT untuk tabel `menus`
--
ALTER TABLE `menus`
  MODIFY `menu_id` int(5) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT untuk tabel `menu_categories`
--
ALTER TABLE `menu_categories`
  MODIFY `category_id` int(3) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT untuk tabel `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `placed_orders`
--
ALTER TABLE `placed_orders`
  MODIFY `order_id` int(5) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=52;

--
-- AUTO_INCREMENT untuk tabel `reservations`
--
ALTER TABLE `reservations`
  MODIFY `reservation_id` int(5) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=110;

--
-- AUTO_INCREMENT untuk tabel `reviews`
--
ALTER TABLE `reviews`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `shipping_surcharges`
--
ALTER TABLE `shipping_surcharges`
  MODIFY `surcharge_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT untuk tabel `shipping_zones`
--
ALTER TABLE `shipping_zones`
  MODIFY `zone_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT untuk tabel `tables`
--
ALTER TABLE `tables`
  MODIFY `table_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(2) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT untuk tabel `website_settings`
--
ALTER TABLE `website_settings`
  MODIFY `option_id` int(5) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `in_order`
--
ALTER TABLE `in_order`
  ADD CONSTRAINT `fk_menu` FOREIGN KEY (`menu_id`) REFERENCES `menus` (`menu_id`),
  ADD CONSTRAINT `fk_order` FOREIGN KEY (`order_id`) REFERENCES `placed_orders` (`order_id`);

--
-- Ketidakleluasaan untuk tabel `menus`
--
ALTER TABLE `menus`
  ADD CONSTRAINT `FK_menu_category_id` FOREIGN KEY (`category_id`) REFERENCES `menu_categories` (`category_id`);

--
-- Ketidakleluasaan untuk tabel `menu_ingredients`
--
ALTER TABLE `menu_ingredients`
  ADD CONSTRAINT `menu_ingredients_ibfk_1` FOREIGN KEY (`menu_id`) REFERENCES `menus` (`menu_id`),
  ADD CONSTRAINT `menu_ingredients_ibfk_2` FOREIGN KEY (`ingredient_id`) REFERENCES `ingredients` (`id`);

--
-- Ketidakleluasaan untuk tabel `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`),
  ADD CONSTRAINT `notifications_ibfk_2` FOREIGN KEY (`order_id`) REFERENCES `placed_orders` (`order_id`);

--
-- Ketidakleluasaan untuk tabel `placed_orders`
--
ALTER TABLE `placed_orders`
  ADD CONSTRAINT `fk_client` FOREIGN KEY (`client_id`) REFERENCES `clients` (`client_id`),
  ADD CONSTRAINT `fk_reservation` FOREIGN KEY (`reservation_id`) REFERENCES `reservations` (`reservation_id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `reservations`
--
ALTER TABLE `reservations`
  ADD CONSTRAINT `fk_reservation_client` FOREIGN KEY (`client_id`) REFERENCES `clients` (`client_id`);

--
-- Ketidakleluasaan untuk tabel `reviews`
--
ALTER TABLE `reviews`
  ADD CONSTRAINT `reviews_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`),
  ADD CONSTRAINT `reviews_ibfk_2` FOREIGN KEY (`menu_id`) REFERENCES `menus` (`menu_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
