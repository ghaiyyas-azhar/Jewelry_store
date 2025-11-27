<?php
// Cek session admin, pastikan hanya admin yang bisa akses
session_name("admin_session"); // Menentukan nama session untuk admin
session_start(); // Memulai session
if (!isset($_SESSION['admin_id'])) { // Jika admin belum login
    header('Location: admin_login.php'); // Arahkan ke halaman login
    exit(); // Hentikan eksekusi kode selanjutnya
}

include '../koneksi.php'; // Hubungkan file koneksi database

// --- LOGIKA UNTUK MENAMBAH CUSTOMER BARU ---
if (isset($_POST['add_customer'])) { // Jika tombol tambah customer ditekan
    $name = $_POST['name']; // Ambil input nama
    $email = $_POST['email']; // Ambil input email
    $phone = $_POST['phone']; // Ambil input nomor telepon
    $password = $_POST['password']; // Ambil input password
    $address = $_POST['address']; // Ambil input alamat

    // Cek email udh terdaftar
    $check_email_stmt = $conn->prepare("SELECT customer_id FROM customer WHERE email = ?"); // Siapkan query untuk cek email
    $check_email_stmt->bind_param("s", $email); // Bind parameter email
    $check_email_stmt->execute(); // Jalankan query
    $check_email_stmt->store_result(); // Simpan hasil

    if ($check_email_stmt->num_rows > 0) { // Jika email sudah terdaftar
        $_SESSION['error'] = "Email sudah terdaftar. Gunakan email lain."; // Tampilkan pesan error
    } else {
        // Jika email unik, lanjutkan insert
        $hashed_password = password_hash($password, PASSWORD_DEFAULT); // Enkripsi password

        $insert_stmt = $conn->prepare("INSERT INTO customer (name, email, phone, password, address) VALUES (?, ?, ?, ?, ?)"); // Siapkan query tambah data
        $insert_stmt->bind_param("sssss", $name, $email, $phone, $hashed_password, $address); // Bind parameter data

        if ($insert_stmt->execute()) { // Jalankan query
            $_SESSION['message'] = "Customer baru berhasil ditambahkan!"; // Jika berhasil
        } else {
            $_SESSION['error'] = "Gagal menambahkan customer: " . $conn->error; // Jika gagal
        }
        $insert_stmt->close(); // Tutup statement insert
    }
    $check_email_stmt->close(); // Tutup statement cek email

    header("Location: admin_editcustomer.php"); // Redirect ke halaman admin_editcustomer
    exit(); // Hentikan eksekusi
}

// --- LOGIKA UNTUK MEMPROSES EDIT CUSTOMER ---
if (isset($_POST['update_customer'])) { // Jika tombol update ditekan
    $customer_id_to_update = $_POST['customer_id']; // Ambil ID customer yang mau diubah
    $name = $_POST['name']; // Ambil nama baru
    $email = $_POST['email']; // Ambil email baru
    $phone = $_POST['phone']; // Ambil telepon baru
    $address = $_POST['address']; // Ambil alamat baru
    $password = $_POST['password']; // Ambil password baru dari form
    
    // Cek apakah password diisi
    if (!empty($password)) { // Jika password diisi
        // Jika password diisi, hash password baru
        $hashed_password = password_hash($password, PASSWORD_DEFAULT); // Enkripsi password baru
        // Update semua termasuk password
        $update_stmt = $conn->prepare("UPDATE customer SET name = ?, email = ?, phone = ?, address = ?, password = ? WHERE customer_id = ?"); // Query update dengan password
        $update_stmt->bind_param("sssssi", $name, $email, $phone, $address, $hashed_password, $customer_id_to_update); // Bind data
    } else {
        // Jika password kosong, update tanpa mengubah password
        $update_stmt = $conn->prepare("UPDATE customer SET name = ?, email = ?, phone = ?, address = ? WHERE customer_id = ?"); // Query update tanpa password
        $update_stmt->bind_param("ssssi", $name, $email, $phone, $address, $customer_id_to_update); // Bind data
    }
    
    if ($update_stmt->execute()) { // Jalankan query update
        $_SESSION['message'] = "Data customer berhasil diperbarui!"; // Pesan sukses
    } else {
        $_SESSION['error'] = "Gagal memperbarui data customer: " . $conn->error; // Pesan error
    }
    $update_stmt->close(); // Tutup statement update
    
    header("Location: admin_editcustomer.php"); // Redirect ke halaman edit
    exit(); // Hentikan eksekusi
}

// --- LOGIKA UNTUK MENGHAPUS CUSTOMER ---
if (isset($_GET['delete_customer_id'])) { // Jika tombol hapus ditekan
    $delete_id = $_GET['delete_customer_id']; // Ambil ID customer
    
    // Hapus semua order terkait customer ini terlebih dahulu
    $delete_bookings_stmt = $conn->prepare("DELETE FROM booking_order WHERE customer_id = ?"); // Hapus semua data booking terkait
    $delete_bookings_stmt->bind_param("i", $delete_id); // Bind ID customer
    $delete_bookings_stmt->execute(); // Jalankan query
    $delete_bookings_stmt->close(); // Tutup statement
    
    // Kemudian hapus customer
    $delete_stmt = $conn->prepare("DELETE FROM customer WHERE customer_id = ?"); // Query hapus customer
    $delete_stmt->bind_param("i", $delete_id); // Bind ID
    if ($delete_stmt->execute()) { // Jalankan query
        $_SESSION['message'] = "Customer berhasil dihapus!"; // Pesan sukses
    } else {
        $_SESSION['error'] = "Gagal menghapus customer: " . $conn->error; // Pesan error
    }
    $delete_stmt->close(); // Tutup statement
    
    header("Location: admin_editcustomer.php"); // Redirect
    exit(); // Hentikan eksekusi
}

// --- LOGIKA: Ambil Semua Data Customer untuk Ditampilkan ---
 $customers = []; // Buat array kosong untuk menyimpan data customer
 $customers_stmt = $conn->prepare("SELECT customer_id, name, email, phone, address FROM customer ORDER BY name"); // Query ambil semua data customer
 $customers_stmt->execute(); // Jalankan query
 $customers_result = $customers_stmt->get_result(); // Ambil hasil query
 $customers = $customers_result->fetch_all(MYSQLI_ASSOC); // Simpan hasil ke array asosiatif
 $customers_stmt->close(); // Tutup statement

 $conn->close(); // Tutup koneksi database
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Customers - Nahecididi Jewelry</title>
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
            <a href="admin_collection.php">edit collection</a>
            <a href="admin_editcustomer.php" class="active">Manage Customers</a>
            <a href="admin_manage_booking.php">Manage Booking</a>
        </nav>
        <div class="icons">
            <a href="admin_logout.php" class="bx bx-log-out" title="Logout"></a>
        </div>
    </header>
<br><br><br><br>
    <!-- MANAGE CUSTOMERS SECTION -->
    <section class="manage-customers">
        <h3 class="sub-heading"></h3>
        <h3 class="sub-heading">Ini Tugas mu ya Atmin</h3>
        <h1 class="heading">Manage Customer Information</h1>
        
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

        <!-- TOMBOL TAMBAH CUSTOMER BARU -->
        <div style="text-align: right; margin-bottom: 1rem;">
            <button class="btn add-btn" onclick="openAddModal()">
                <i class="fas fa-plus"></i> Add New Customer
            </button>
        </div>

        <div class="customer-table-container">
            <table class="customer-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Address</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if (!empty($customers)):
                        foreach ($customers as $customer):
                    ?>
                    <tr>
                        <td><?php echo htmlspecialchars($customer['customer_id']); ?></td>
                        <td><?php echo htmlspecialchars($customer['name']); ?></td>
                        <td><?php echo htmlspecialchars($customer['email']); ?></td>
                        <td><?php echo htmlspecialchars($customer['phone']); ?></td>
                        <td><?php echo htmlspecialchars($customer['address']); ?></td>
                        <td>
                            <button class="btn edit-btn" onclick="openEditModal(
                                '<?php echo $customer['customer_id']; ?>',
                                '<?php echo htmlspecialchars($customer['name'], ENT_QUOTES); ?>',
                                '<?php echo htmlspecialchars($customer['email'], ENT_QUOTES); ?>',
                                '<?php echo htmlspecialchars($customer['phone'], ENT_QUOTES); ?>',
                                '<?php echo htmlspecialchars($customer['address'], ENT_QUOTES); ?>'
                            )">Edit</button>

                            <button class="btn delete-btn" onclick="confirmDelete('<?php echo $customer['customer_id']; ?>')">Delete</button>
                        </td>
                    </tr>
                    <?php
                        endforeach;
                    else:
                    ?>
                    <tr>
                        <td colspan="6" style="text-align: center; padding: 2rem;">Tidak ada data customer.</td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>

    <!-- Modal untuk ADD Customer -->
    <div id="addModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Add New Customer</h2>
                <span class="close-btn" onclick="closeAddModal()">&times;</span>
            </div>
            <form action="admin_editcustomer.php" method="POST">
                <div class="input-group">
                    <label for="add_name">Name</label>
                    <input type="text" id="add_name" name="name" required>
                </div>
                <div class="input-group">
                    <label for="add_email">Email</label>
                    <input type="email" id="add_email" name="email" required>
                </div>
                <div class="input-group">
                    <label for="add_phone">Phone</label>
                    <input type="text" id="add_phone" name="phone" required>
                </div>
                <div class="input-group">
                    <label for="add_address">Address</label>
                    <input type="text" id="add_address" name="address" required>
                </div>
                <div class="input-group">
                    <label for="add_password">Password</label>
                    <input type="password" id="add_password" name="password" required>
                </div>
                <div class="modal-footer">
                    <button type="submit" name="add_customer" class="btn">Add Customer</button>
                    <button type="button" class="btn btn-cancel" onclick="closeAddModal()">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal untuk Edit Customer -->
    <div id="editModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Edit Customer Information</h2>
                <span class="close-btn" onclick="closeEditModal()">&times;</span>
            </div>
            <form action="admin_editcustomer.php" method="POST">
                <input type="hidden" name="customer_id" id="edit_customer_id">
                <div class="input-group">
                    <label for="edit_name">Name</label>
                    <input type="text" id="edit_name" name="name" required>
                </div>
                <div class="input-group">
                    <label for="edit_email">Email</label>
                    <input type="email" id="edit_email" name="email" required>
                </div>
                <div class="input-group">
                    <label for="edit_phone">Phone</label>
                    <input type="text" id="edit_phone" name="phone" required>
                </div>
                <div class="input-group">
                    <label for="edit_address">Address</label>
                    <input type="text" id="edit_address" name="address" required>
                </div>
                <div class="input-group">
                    <label for="edit_password">Password (Kosongkan jika tidak ingin mengubah)</label>
                    <input type="password" id="edit_password" name="password">
                </div>
                <div class="modal-footer">
                    <button type="submit" name="update_customer" class="btn">Save Changes</button>
                    <button type="button" class="btn btn-cancel" onclick="closeEditModal()">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Tambahkan CSS dan JavaScript -->
    <style>
        .manage-customers { padding: 2rem 9%; }
        .customer-table-container { overflow-x: auto; }
        .customer-table { width: 100%; border-collapse: collapse; margin-top: 1rem; background: #fff; box-shadow: var(--box-shadow); }
        .customer-table th, .customer-table td { padding: 12px 15px; border: 1px solid #ddd; text-align: left; font-size: 1.6rem; }
        .customer-table th { background-color: var(--orange); color: #fff; font-weight: bold; }
        .customer-table tbody tr:nth-child(even) { background-color: #f9f9f9; }
        .customer-table tbody tr:hover { background-color: #f1f1f1; }
        
        /* Style untuk tombol */
        .edit-btn { background-color: #007bff; margin-right: 5px; }
        .edit-btn:hover { background-color: #0056b3; }
        .delete-btn { background-color: #dc3545; }
        .delete-btn:hover { background-color: #c82333; }
        .add-btn { background-color: #28a745; color: white; padding: 10px 20px; font-size: 1.6rem; border: none; border-radius: 5px; cursor: pointer; }
        .add-btn:hover { background-color: #218838; }
        
        /* Style untuk Modal */
        .modal { display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.5); }
        .modal-content { background-color: #fefefe; margin: 10% auto; padding: 0; border-radius: 8px; width: 90%; max-width: 500px; box-shadow: 0 5px 15px rgba(0,0,0,0.3); }
        .modal-header { background-color: var(--orange); color: white; padding: 15px 20px; display: flex; justify-content: space-between; align-items: center; border-radius: 8px 8px 0 0; }
        .modal-header h2 { margin: 0; }
        .close-btn { color: white; font-size: 28px; font-weight: bold; cursor: pointer; }
        .close-btn:hover { opacity: 0.7; }
        .modal-content form { padding: 20px; }
        .input-group { margin-bottom: 15px; }
        .input-group label { display: block; margin-bottom: 5px; font-weight: bold; }
        .input-group input { width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; }
        .modal-footer { text-align: right; padding: 10px 20px; background-color: #f1f1f1; border-radius: 0 0 8px 8px; }
        .btn-cancel { background-color: #6c757d; margin-left: 10px; }
        .navbar a.active { color: var(--orange); background: #eee; }
        .success-message { background-color: #d4edda; color: #155724; padding: 10px; margin-bottom: 20px; border-radius: 5px; text-align: center; }
        .error-message { background-color: #f8d7da; color: #721c24; padding: 10px; margin-bottom: 20px; border-radius: 5px; text-align: center; }
    </style>

    <script>
    // Fungsi untuk Modal Add
    function openAddModal() {
        document.getElementById('addModal').style.display = 'block';
    }
    function closeAddModal() {
        document.getElementById('addModal').style.display = 'none';
        document.getElementById('add_name').value = '';
        document.getElementById('add_email').value = '';
        document.getElementById('add_phone').value = '';
        document.getElementById('add_address').value = '';
        document.getElementById('add_password').value = '';
    }

    // Fungsi untuk Modal Edit
    function openEditModal(id, name, email, phone, address) {
        document.getElementById('edit_customer_id').value = id;
        document.getElementById('edit_name').value = name;
        document.getElementById('edit_email').value = email;
        document.getElementById('edit_phone').value = phone;
        document.getElementById('edit_address').value = address;
        document.getElementById('edit_password').value = '';
        document.getElementById('editModal').style.display = 'block';
    }
    function closeEditModal() {
        document.getElementById('editModal').style.display = 'none';
    }

    // Fungsi untuk Konfirmasi Delete
    function confirmDelete(customerId) {
        if (confirm('Apakah Anda yakin ingin menghapus customer ini? Tindakan ini tidak dapat dibatalkan.')) {
            window.location.href = 'admin_editcustomer.php?delete_customer_id=' + customerId;
        }
    }
    </script>
</body>
</html>