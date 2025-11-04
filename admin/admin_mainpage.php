<?php
// Mengatur nama sesi menjadi "admin_session"
session_name("admin_session");

// Memulai sesi
session_start();

// Mengecek apakah admin sudah login (dengan memeriksa apakah ada 'admin_id' di session)
if (!isset($_SESSION['admin_id'])) {
    // Jika belum login, arahkan ke halaman login admin
    header('Location: admin_login.php');
    exit();
}

// Menyertakan file koneksi ke database
include '../koneksi.php';

// --- LOGIKA: Proses Approve Booking ---
// Mengecek apakah ada parameter 'approve_id' pada URL (menandakan admin ingin menyetujui booking)
if (isset($_GET['approve_id'])) {
    $approve_id = $_GET['approve_id']; // Mengambil ID booking dari parameter URL
    $admin_id = $_SESSION['admin_id']; // Mengambil ID admin dari session (admin yang menyetujui)

    // Membuat query untuk memperbarui status booking menjadi 'approved' dan mencatat admin_id yang menyetujui
    $update_stmt = $conn->prepare("UPDATE booking_order SET status = 'approved', admin_id = ? WHERE booking_id = ?");
    $update_stmt->bind_param("is", $admin_id, $approve_id); // Mengikat parameter admin_id (integer) dan booking_id (string)
    
    // Mengeksekusi query update
    if ($update_stmt->execute()) {
        // Jika berhasil, simpan pesan sukses ke session
        $_SESSION['message'] = "Booking berhasil disetujui!";
    } else {
        // Jika gagal, simpan pesan error ke session
        $_SESSION['error'] = "Gagal menyetujui booking.";
    }
    // Menutup statement
    $update_stmt->close();
    
    // Refresh halaman agar perubahan langsung terlihat
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// --- LOGIKA: Ambil Data Booking Pending ---
// Query untuk menampilkan semua booking yang statusnya masih 'pending' beserta data customer terkait
$query = "SELECT 
           bo.*, 
           c.name, 
           c.email, 
           c.phone 
         FROM booking_order bo 
         JOIN customer c ON bo.customer_id = c.customer_id 
         WHERE bo.status = 'pending' 
         ORDER BY bo.session_date DESC";

// Menyiapkan statement SQL
$stmt = $conn->prepare($query);
if ($stmt === false) {
    // Jika gagal menyiapkan query, hentikan eksekusi dan tampilkan pesan error
    die("Error preparing booking statement: " . $conn->error);
}
$stmt->execute(); // Menjalankan query
$pending_bookings = $stmt->get_result(); // Mengambil hasil data booking pending

// --- LOGIKA: Hitung Customer Online dan Offline (Untuk Tampilan Awal) ---
// Menetapkan batas waktu aktivitas (10 tahun terakhir sebagai contoh)
$time_threshold = date('Y-m-d H:i:s', strtotime('-10 years'));

// Query untuk mengambil data customer yang dianggap "online" (terakhir aktif dalam rentang waktu di atas)
$online_stmt = $conn->prepare("SELECT customer_id, name FROM customer WHERE last_activity > ? AND last_activity IS NOT NULL");
$online_stmt->bind_param("s", $time_threshold);
$online_stmt->execute();
$online_customers = $online_stmt->get_result(); // Menyimpan hasil customer yang online

// Query untuk menghitung jumlah customer "offline"
$offline_stmt = $conn->prepare("SELECT COUNT(*) as total FROM customer WHERE last_activity <= ? OR last_activity IS NULL");
$offline_stmt->bind_param("s", $time_threshold);
$offline_stmt->execute();
$offline_result = $offline_stmt->get_result();
$offline_customers = $offline_result->fetch_assoc(); // Mengambil hasil query dalam bentuk array asosiatif
$offline_count = $offline_customers['total']; // Menyimpan total customer offline
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Nahecididi Jewelry</title>
    <!-- Library dan Icon -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <link rel="stylesheet" href="https://unpkg.com/boxicons@latest/css/boxicons.min.css">
    <link rel="stylesheet" href="../css/admin_style.css">
</head>
<body>
    <!-- Header -->
    <header>
        <a href="admin_mainpage.php" class="Logo">nahecididi jewelry (Admin)</a>
        <nav class="navbar">
            <a href="admin_mainpage.php" class="active">Dashboard</a>
            <a href="admin_collection.php">Edit Collection</a>
            <a href="admin_editcustomer.php">Customer Info</a>
            <a href="admin_manage_booking.php">Manage Booking</a>
        </nav>
        <div class="icons">
            <i class="fas fa-bars" id="menu-bars"></i>
            <a href="admin_logout.php" class="bx bx-log-out" title="Logout"></a>
        </div>
    </header>

    <!-- CUSTOMER STATUS SECTION -->
    <br><br><br><br><br>
    <section class="customer-status" id="customer-status">
        <h3 class="sub-heading">Ini Tugas mu ya Atmin</h3>
        <h1 class="heading">Customer Status Overview</h1>
        <div class="row">
            <div class="status-box">
                <div class="status-icon online">
                    <i class="fas fa-users"></i>
                    <span class="live-indicator"></span> <!-- Indikator Live -->
                </div>
                <div class="status-info">
                    <h3 id="online-count"><?php echo $online_customers->num_rows; ?></h3>
                    <p>Customers Online</p>
                </div>
            </div>
            <div class="status-box">
                <div class="status-icon offline">
                    <i class="fas fa-user-slash"></i>
                </div>
                <div class="status-info">
                    <h3 id="offline-count"><?php echo $offline_count; ?></h3>
                    <p>Customers Offline</p>
                </div>
            </div>
        </div>
        
        <div id="online-list-container" class="online-list">
            <!-- Konten akan diisi oleh JavaScript -->
        </div>
    </section>

    <section class="booking-confirmation" id="booking-confirmation">
        <h3 class="sub-heading">Ini juga Tugas mu Atmin</h3>
        <h1 class="heading">Konfirmasi Booking</h1>
        <?php
        if (isset($_SESSION['message'])) {
            echo '<p class="success-message">' . $_SESSION['message'] . '</p>';
            unset($_SESSION['message']);
        }
        if (isset($_SESSION['error'])) {
            echo '<p class="error-message">' . $_SESSION['error'] . '</p>';
            unset($_SESSION['error']);
        }
        ?>

        <div class="booking-list">
            <?php if ($pending_bookings->num_rows > 0): ?>
                <?php while ($booking = $pending_bookings->fetch_assoc()): ?>
                    <div class="booking-item">
                        <div class="booking-info">
                            <h4>Booking ID: <?php echo htmlspecialchars($booking['booking_id']); ?></h4>
                            
                            <p><strong>Detail Customer:</strong></p>
                            <ul style="list-style: none; padding-left: 0;">
                                <li>Nama: <strong><?php echo htmlspecialchars($booking['name']); ?></strong></li>
                                <li>Email: <?php echo htmlspecialchars($booking['email']); ?></li>
                                <li>Telepon: <?php echo htmlspecialchars($booking['phone']); ?></li>
                            </ul>
                            
                            <p><strong>Tanggal & Waktu Sesi:</strong> <?php echo htmlspecialchars($booking['session_date']); ?> at <?php echo htmlspecialchars($booking['session_time']); ?></p>
                            
                            <p><strong>Detail Pesanan:</strong><br><?php echo nl2br(htmlspecialchars($booking['order_details'])); ?></p>
                        </div>
                        <a href="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>?approve_id=<?php echo urlencode($booking['booking_id']); ?>" class="btn approve-btn">Setujui</a>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p>Tidak ada booking yang menunggu persetujuan.</p>
            <?php endif; ?>
        </div>
    </section>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
    <script src="../js/main.js"></script>
    
    <script>
    function updateCustomerStatus() {
        fetch('get_customer_status.php')
            .then(response => response.json())
            .then(data => {
                // Update counts
                document.getElementById('online-count').innerText = data.online_count;
                document.getElementById('offline-count').innerText = data.offline_count;

                // Update the list of online customers
                const onlineListContainer = document.getElementById('online-list-container');
                if (data.online_count > 0) {
                    let listHTML = '<h4>Currently Online:</h4><ul>';
                    data.online_names.forEach(name => {
                        listHTML += `<li>${name}</li>`;
                    });
                    listHTML += '</ul>';
                    onlineListContainer.innerHTML = listHTML;
                } else {
                    onlineListContainer.innerHTML = ''; // Sembunyikan jika tidak ada yang online
                }
            })
            .catch(error => console.error('Error fetching customer status:', error));
    }

    // Panggil fungsi saat halaman dimuat
    document.addEventListener('DOMContentLoaded', function() {
        updateCustomerStatus(); // Panggil sekali di awal
        // Set interval untuk memperbarui setiap 30 detik (30000 milliseconds)
        setInterval(updateCustomerStatus, 30000);
    });
    </script>
    
    <style>
        /* ... (CSS Anda yang lain tetap sama) ... */
        .customer-status { padding: 2rem 9%; }
        .customer-status .row {
            display: flex;
            gap: 2rem;
            justify-content: center;
            flex-wrap: wrap;
        }
        .status-box {
            background: #fff;
            border-radius: .5rem;
            box-shadow: var(--box-shadow);
            padding: 2rem;
            text-align: center;
            flex: 1;
            min-width: 200px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 1.5rem;
            position: relative; /* Tambahkan ini untuk indikator */
        }
        .status-icon {
            font-size: 4rem;
            width: 8rem;
            height: 8rem;
            line-height: 8rem;
            border-radius: 50%;
            color: #fff;
            position: relative; /* Tambahkan ini */
        }
        .status-icon.online { background: #28a745; }
        .status-icon.offline { background: #dc3545; }
        
        /* Indikator Live */
        .live-indicator {
            position: absolute;
            top: 10px;
            right: 10px;
            width: 1.5rem;
            height: 1.5rem;
            background-color: #fff;
            border-radius: 50%;
            animation: pulse 2s infinite;
        }
        @keyframes pulse {
            0% { box-shadow: 0 0 0 0 rgba(40, 167, 69, 0.7); }
            70% { box-shadow: 0 0 0 10px rgba(40, 167, 69, 0); }
            100% { box-shadow: 0 0 0 0 rgba(40, 167, 69, 0); }
        }

        .status-info h3 { font-size: 3rem; color: var(--orange); margin: 0; }
        .status-info p { font-size: 1.6rem; margin: .5rem 0 0 0; }
        .online-list {
            margin-top: 2rem;
            background: #fff;
            padding: 1.5rem;
            border-radius: .5rem;
            box-shadow: var(--box-shadow);
        }
        .online-list h4 { font-size: 1.8rem; color: #333; margin-bottom: 1rem; }
        .online-list ul { list-style: none; padding: 0; display: flex; flex-wrap: wrap; gap: 1rem; }
        .online-list li {
            background: #f0f0f0;
            padding: .5rem 1rem;
            border-radius: 20px;
            font-size: 1.4rem;
        }
        .booking-list { 
            max-width: 800px; 
            max-height: 500px;
            overflow-y: auto;   
            margin: auto; 
            padding-right: 10px; 
        }
        .booking-item { 
            background: #f9f9f9; 
            border: 1px solid #ddd; 
            padding: 20px; 
            margin-bottom: 15px; 
            border-radius: 8px;
            display: flex; 
            justify-content: space-between; 
            align-items: center; }
        .booking-info h4 { 
            margin: 0 0 10px 0; 
            color: #e84393; }
        .booking-info p { 
            margin: 5px 0; 
            line-height: 1.6; }
        .approve-btn { 
            background-color: #28a745; 
            color: white; padding: 10px 20px; 
            text-decoration: none; 
            border-radius: 5px; }
        .approve-btn:hover { 
            background-color: #218838; }
        .success-message { 
            background: #d4edda; 
            color: #155724; 
            padding: 10px; 
            border: 1px solid #c3e6cb; 
            border-radius: 5px; 
            margin-bottom: 20px; }
        .error-message { 
            background: #f8d7da; 
            color: #721c24; 
            padding: 10px; 
            border: 1px solid #f5c6cb; 
            border-radius: 5px; 
            margin-bottom: 20px; }
    </style>
</body>
</html>
<?php 
 $stmt->close(); 
 $online_stmt->close();
 $offline_stmt->close();
 $conn->close(); 
?>