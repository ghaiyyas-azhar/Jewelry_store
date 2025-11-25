<?php
// Menetapkan nama sesi khusus untuk admin
session_name("admin_session");

// Memulai sesi
session_start();

// Mengecek apakah admin sudah login
if (!isset($_SESSION['admin_id'])) {
    // Jika belum login, arahkan ke halaman login admin
    header('Location: admin_login.php');
    exit();
}

// Menyertakan file koneksi database
include '../koneksi.php';

// --- LOGIKA UNTUK MENANGANI AKSI BOOKING ---

// 1. Proses Approve Booking
// Mengecek apakah parameter approve_id dikirim melalui URL
if (isset($_GET['approve_id'])) {
    $approve_id = $_GET['approve_id']; // Menyimpan ID booking yang ingin disetujui
    $admin_id = $_SESSION['admin_id']; // Menyimpan ID admin dari sesi

    // Menyiapkan query untuk mengubah status booking menjadi 'approved' dan mencatat admin_id
    $update_stmt = $conn->prepare("UPDATE booking_order SET status = 'approved', admin_id = ? WHERE booking_id = ?");
    $update_stmt->bind_param("is", $admin_id, $approve_id); // Mengikat parameter

    // Menjalankan query
    if ($update_stmt->execute()) {
        // Jika berhasil, tampilkan pesan sukses
        $_SESSION['message'] = "Booking berhasil disetujui!";
    } else {
        // Jika gagal, tampilkan pesan error
        $_SESSION['error'] = "Gagal menyetujui booking.";
    }
    $update_stmt->close(); // Menutup statement

    // Refresh halaman agar data terbaru langsung muncul
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// 2. Proses Cancel Booking (via POST dari modal)
// Mengecek apakah form cancel dikirim (tombol cancel_booking ditekan)
if (isset($_POST['cancel_booking'])) {
    $booking_id_to_cancel = $_POST['booking_id']; // Menyimpan ID booking yang akan dibatalkan
    $admin_id = $_SESSION['admin_id']; // Menyimpan ID admin dari sesi

    // Query untuk mengubah status booking menjadi 'canceled'
    $update_stmt = $conn->prepare("UPDATE booking_order SET status = 'canceled', admin_id = ? WHERE booking_id = ?");
    $update_stmt->bind_param("is", $admin_id, $booking_id_to_cancel);

    // Jalankan query
    if ($update_stmt->execute()) {
        // Jika berhasil, tampilkan pesan sukses
        $_SESSION['message'] = "Booking berhasil dibatalkan.";
    } else {
        // Jika gagal, tampilkan pesan error
        $_SESSION['error'] = "Gagal membatalkan booking.";
    }
    $update_stmt->close(); // Menutup statement
    
    // Reload halaman untuk memperbarui data
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// 3. Proses Delete Booking
// Mengecek apakah parameter delete_id dikirim melalui URL
if (isset($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id']; // Menyimpan ID booking yang ingin dihapus

    // Query untuk menghapus data booking berdasarkan ID
    $delete_stmt = $conn->prepare("DELETE FROM booking_order WHERE booking_id = ?");
    $delete_stmt->bind_param("s", $delete_id);

    // Jalankan query delete
    if ($delete_stmt->execute()) {
        // Jika berhasil, tampilkan pesan sukses
        $_SESSION['message'] = "Booking berhasil dihapus permanen.";
    } else {
        // Jika gagal, tampilkan pesan error
        $_SESSION['error'] = "Gagal menghapus booking.";
    }
    $delete_stmt->close(); // Menutup statement
    
    // Reload halaman
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// Query untuk mengambil SEMUA data booking dari database
// Termasuk data customer dan admin yang menangani booking
$all_booking_query = "SELECT 
                        bo.*, 
                        c.name as customer_name, 
                        c.email, 
                        c.phone,
                        a.name as admin_name
                      FROM booking_order bo 
                      JOIN customer c ON bo.customer_id = c.customer_id 
                      LEFT JOIN admin a ON bo.admin_id = a.admin_id
                      ORDER BY 
                        CASE bo.status 
                            WHEN 'pending' THEN 1 
                            WHEN 'approved' THEN 2 
                            WHEN 'canceled' THEN 3 
                        END,
                        bo.session_date DESC";

// Menjalankan query untuk mengambil seluruh data booking
$all_booking_result = $conn->query($all_booking_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage All booking - Nahecididi Jewelry</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <link rel="stylesheet" href="https://unpkg.com/boxicons@latest/css/boxicons.min.css">
    <link rel="stylesheet" href="../css/admin_style.css">
</head>
<body>
    <!-- Header -->
    <header>
        <a href="admin_mainpage.php" class="Logo">nahecididi jewelry (Admin)</a>
        <nav class="navbar">
            <a href="admin_mainpage.php">Dashboard</a>
            <a href="admin_collection.php">Edit Collection</a>
            <a href="admin_editcustomer.php">Customer Info</a>
            <a href="admin_manage_booking.php" class="active">Manage Booking</a>
        </nav>
        <div class="icons">
            <i class="fas fa-bars" id="menu-bars"></i>
            <a href="admin_logout.php" class="bx bx-log-out" title="Logout"></a>
        </div>
    </header>

    <br><br><br><br><br>
    <!-- MANAGE booking SECTION -->
    <section class="manage-booking">
        <h3 class="sub-heading">Ini Tugas mu ya Atmin</h3>
        <h1 class="heading">Manage All booking</h1>
        
        <?php
        // Tampilkan pesan sukses atau error
        if (isset($_SESSION['message'])) {
            echo '<p class="success-message">' . $_SESSION['message'] . '</p>';
            unset($_SESSION['message']);
        }
        if (isset($_SESSION['error'])) {
            echo '<p class="error-message">' . $_SESSION['error'] . '</p>';
            unset($_SESSION['error']);
        }
        ?>

        <div class="booking-table-container">
            <table class="booking-table">
                <thead>
                    <tr>
                        <th>Booking ID</th>
                        <th>Customer</th>
                        <th>Session Date</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($all_booking_result && $all_booking_result->num_rows > 0): ?>
                        <?php while ($booking = $all_booking_result->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($booking['booking_id']); ?></td>
                                <td>
                                    <strong><?php echo htmlspecialchars($booking['customer_name']); ?></strong><br>
                                    <small><?php echo htmlspecialchars($booking['email']); ?></small>
                                </td>
                                <td><?php echo htmlspecialchars($booking['session_date']); ?> <br> <?php echo htmlspecialchars($booking['session_time']); ?></td>
                                <td>
                                    <?php
                                    $status_class = '';
                                    $status_icon = '';
                                    switch ($booking['status']) {
                                        case 'pending':
                                            $status_class = 'pending';
                                            $status_icon = 'fas fa-clock';
                                            break;
                                        case 'approved':
                                            $status_class = 'approved';
                                            $status_icon = 'fas fa-check-circle';
                                            break;
                                        case 'canceled':
                                            $status_class = 'canceled';
                                            $status_icon = 'fas fa-times-circle';
                                            break;
                                    }
                                    ?>
                                    <span class="status-badge <?php echo $status_class; ?>">
                                        <i class="<?php echo $status_icon; ?>"></i>
                                        <?php echo ucfirst(htmlspecialchars($booking['status'])); ?>
                                    </span>
                                </td>
                                <td class="actions-cell">
                                    <?php if ($booking['status'] == 'pending'): ?>
                                        <a href="?approve_id=<?php echo urlencode($booking['booking_id']); ?>" class="btn-action approve" title="Approve"><i class="fas fa-check"></i></a>
                                        <button class="btn-action cancel" onclick="openCancelModal('<?php echo $booking['booking_id']; ?>')" title="Cancel"><i class="fas fa-ban"></i></button>
                                    <?php elseif ($booking['status'] == 'approved'): ?>
                                        <button class="btn-action cancel" onclick="openCancelModal('<?php echo $booking['booking_id']; ?>')" title="Cancel"><i class="fas fa-ban"></i></button>
                                    <?php endif; ?>
                                    <button class="btn-action delete" onclick="confirmDelete('<?php echo $booking['booking_id']; ?>')" title="Delete"><i class="fas fa-trash"></i></button>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" style="text-align: center; padding: 2rem;">Belum ada data booking.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>

    <!-- Modal untuk Cancel Booking -->
    <div id="cancelModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Cancel Booking</h2>
                <span class="close-btn" onclick="closeCancelModal()">&times;</span>
            </div>
            <form action="admin_manage_booking.php" method="POST">
                <input type="hidden" name="booking_id" id="cancel_booking_id">
                <div class="input-group">
                    <label for="cancellation_reason">Alasan Pembatalan</label>
                    <textarea id="cancellation_reason" name="cancellation_reason" rows="4" placeholder="Masukkan alasan pembatalan..."></textarea>
                </div>
                <div class="modal-footer">
                    <button type="submit" name="cancel_booking" class="btn btn-danger">Confirm Cancellation</button>
                    <button type="button" class="btn btn-secondary" onclick="closeCancelModal()">Close</button>
                </div>
            </form>
        </div>
    </div>


    <!-- JS Libraries -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="../js/main.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <!-- Tambahkan CSS dan JavaScript untuk tampilan baru -->
    <style>
        .manage-booking { padding: 2rem 9%; }
        .booking-table-container { overflow-x: auto; }
        .booking-table { width: 100%; border-collapse: collapse; margin-top: 1rem; background: #fff; box-shadow: var(--box-shadow); }
        .booking-table th, .booking-table td { padding: 12px 15px; border: 1px solid #ddd; text-align: left; font-size: 1.6rem; }
        .booking-table th { background-color: var(--orange); color: #fff; font-weight: bold; }
        .booking-table tbody tr:nth-child(even) { background-color: #f9f9f9; }
        .booking-table tbody tr:hover { background-color: #f1f1f1; }
        
        .status-badge {
            padding: 4px 10px;
            border-radius: 12px;
            font-size: 1.3rem;
            font-weight: bold;
            white-space: nowrap;
        }
        .status-badge.pending { background-color: #ffc107; color: #212529; }
        .status-badge.approved { background-color: #28a745; color: white; }
        .status-badge.canceled { background-color: #dc3545; color: white; }

        .actions-cell { white-space: nowrap; }
        .btn-action {
            border: none;
            padding: 8px 10px;
            margin-right: 5px;
            border-radius: 5px;
            cursor: pointer;
            color: white;
            text-decoration: none;
            display: inline-block;
            transition: background-color 0.3s;
        }
        .btn-action.approve { background-color: #28a745; }
        .btn-action.approve:hover { background-color: #218838; }
        .btn-action.cancel { background-color: #ffc107; color: #212529; }
        .btn-action.cancel:hover { background-color: #e0a800; }
        .btn-action.delete { background-color: #dc3545; }
        .btn-action.delete:hover { background-color: #c82333; }

        /* Modal Style */
        .modal { display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.5); }
        .modal-content { background-color: #fefefe; margin: 10% auto; padding: 0; border-radius: 8px; width: 90%; max-width: 500px; box-shadow: 0 5px 15px rgba(0,0,0,0.3); }
        .modal-header { background-color: var(--orange); color: white; padding: 15px 20px; display: flex; justify-content: space-between; align-items: center; border-radius: 8px 8px 0 0; }
        .modal-header h2 { margin: 0; font-size: 1.8rem; }
        .close-btn { color: white; font-size: 28px; font-weight: bold; cursor: pointer; }
        .close-btn:hover { opacity: 0.7; }
        .modal-content form { padding: 20px; }
        .input-group { margin-bottom: 15px; }
        .input-group label { display: block; margin-bottom: 5px; font-weight: bold; }
        .input-group textarea { width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; font-family: inherit; }
        .modal-footer { text-align: right; padding: 10px 20px; background-color: #f1f1f1; border-radius: 0 0 8px 8px; }
        .btn-danger { background-color: #dc3545; color: white; padding: 8px 15px; border: none; border-radius: 5px; cursor: pointer; }
        .btn-secondary { background-color: #6c757d; color: white; padding: 8px 15px; border: none; border-radius: 5px; cursor: pointer; margin-left: 10px; }

        .success-message { background: #d4edda; color: #155724; padding: 10px; border: 1px solid #c3e6cb; border-radius: 5px; margin-bottom: 20px; }
        .error-message { background: #f8d7da; color: #721c24; padding: 10px; border: 1px solid #f5c6cb; border-radius: 5px; margin-bottom: 20px; }
        .navbar a.active { color: var(--orange); background: #eee; }

.manage-all-btn {
    background-color: #007bff;
    color: white;
    padding: 12px 25px;
    text-decoration: none;
    border-radius: 5px;
    font-size: 1.6rem;
    display: inline-block;
    transition: background-color 0.3s;
}
.manage-all-btn:hover {
    background-color: #0056b3;
}
    </style>
    
    <script>
        // Fungsi untuk Modal Cancel
        function openCancelModal(bookingId) {
            document.getElementById('cancel_booking_id').value = bookingId;
            document.getElementById('cancelModal').style.display = 'block';
        }
        function closeCancelModal() {
            document.getElementById('cancelModal').style.display = 'none';
            document.getElementById('cancellation_reason').value = '';
        }

        // Fungsi untuk Konfirmasi Delete
        function confirmDelete(bookingId) {
            Swal.fire({
                title: 'Apakah Anda yakin?',
                text: "Anda tidak akan dapat mengembalikan booking ini!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Ya, hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = '?delete_id=' + bookingId;
                }
            });
        }
    </script>
</body>
</html>
<?php $conn->close(); ?>