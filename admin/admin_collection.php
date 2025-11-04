<?php
session_name("admin_session"); // Menentukan nama sesi khusus untuk admin
session_start(); // Memulai sesi

if (!isset($_SESSION['admin_id'])) { // Jika admin belum login
    header('Location: admin_login.php'); // Arahkan ke halaman login
    exit();
}

include '../koneksi.php'; // Koneksi ke database


// ===================== HAPUS PRODUK =====================
if (isset($_GET['delete_id'])) { // Jika parameter delete_id dikirim lewat URL
    $product_id_to_delete = $_GET['delete_id'];
    $delete_stmt = $conn->prepare("DELETE FROM collection WHERE collection_id = ?"); // Siapkan query hapus
    $delete_stmt->bind_param("i", $product_id_to_delete); // Bind parameter id

    if ($delete_stmt->execute()) { // Jika berhasil
        $_SESSION['message'] = "Produk berhasil dihapus!";
    } else {
        $_SESSION['error'] = "Gagal menghapus produk.";
    }
    $delete_stmt->close();
    
    header("Location: admin_collection.php"); // Kembali ke halaman koleksi admin
    exit();
}


// ===================== TAMBAH PRODUK =====================
if (isset($_POST['add_product'])) {
    $name = $_POST['name'];
    $description = $_POST['description'];
    $price = $_POST['price'];
    $image_name = $_FILES['image']['name'];
    $image_temp = $_FILES['image']['tmp_name'];
    $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif']; // Ekstensi gambar yang diperbolehkan
    $image_extension = strtolower(pathinfo($image_name, PATHINFO_EXTENSION));
    
    if (in_array($image_extension, $allowed_extensions)) { // Validasi ekstensi
        $new_image_name = uniqid('product_', true) . '.' . $image_extension; // Bikin nama file unik
        $upload_path = '../image/' . $new_image_name;

        if (move_uploaded_file($image_temp, $upload_path)) { // Upload gambar
            $insert_stmt = $conn->prepare("INSERT INTO collection (product_name, description, price, image) VALUES (?, ?, ?, ?)");
            $insert_stmt->bind_param("ssds", $name, $description, $price, $new_image_name);
            
            if ($insert_stmt->execute()) {
                $_SESSION['message'] = "Produk baru berhasil ditambahkan!";
            } else {
                $_SESSION['error'] = "Gagal menyimpan data produk ke database.";
            }
            $insert_stmt->close();
        } else {
            $_SESSION['error'] = "Gagal mengupload gambar.";
        }
    } else {
        $_SESSION['error'] = "Format gambar tidak valid. Hanya diperbolehkan JPG, JPEG, PNG, GIF.";
    }
    
    header("Location: admin_collection.php");
    exit();
}


// ===================== EDIT PRODUK =====================
if (isset($_POST['update_product'])) {
    $product_id_to_update = $_POST['product_id'];
    $name = $_POST['name'];
    $description = $_POST['description'];
    $price = $_POST['price'];
    $old_image = $_POST['old_image']; // Nama gambar lama
    $new_image_name = $old_image; // Default pakai gambar lama

    if (!empty($_FILES['image']['name'])) { // Cek jika ada gambar baru diupload
        $image_extension = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        if (in_array($image_extension, ['jpg', 'jpeg', 'png', 'gif'])) {
            $new_image_name = uniqid('product_', true) . '.' . $image_extension;
            $upload_path = '../image/' . $new_image_name;
            if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_path)) {
                if (file_exists('../image/' . $old_image)) { // Hapus gambar lama
                    unlink('../image/' . $old_image);
                }
            } else {
                $_SESSION['error'] = "Gagal mengupload gambar baru. Data produk tidak diperbarui.";
                header("Location: admin_collection.php");
                exit();
            }
        } else {
            $_SESSION['error'] = "Format gambar baru tidak valid.";
            header("Location: admin_collection.php");
            exit();
        }
    }

    $update_stmt = $conn->prepare("UPDATE collection SET product_name = ?, description = ?, price = ?, image = ? WHERE collection_id = ?");
    $update_stmt->bind_param("ssdsi", $name, $description, $price, $new_image_name, $product_id_to_update);
    
    if ($update_stmt->execute()) {
        $_SESSION['message'] = "Data produk berhasil diperbarui!";
    } else {
        $_SESSION['error'] = "Gagal memperbarui data produk.";
    }
    $update_stmt->close();
    
    header("Location: admin_collection.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Collection - Nahecididi Jewelry</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <link rel="stylesheet" href="https://unpkg.com/boxicons@latest/css/boxicons.min.css">
    <link rel="stylesheet" href="../css/admin_style.css">
</head>
<body>
    <header>
        <a href="admin_mainpage.php" class="Logo">nahecididi jewelry (Admin)</a>
        <nav class="navbar">
            <a href="admin_mainpage.php">Dashboard</a>
            <a href="admin_collection.php" class="active">Edit Collection</a>
            <a href="admin_editcustomer.php">Manage Customers</a>
            <a href="admin_manage_booking.php">Manage Booking</a>
        </nav>
        <div class="icons">
            <a href="admin_logout.php" class="bx bx-log-out" title="Logout"></a>
        </div>
    </header>
<br><br><br><br>
    <section class="manage-collection">
        <h3 class="sub-heading">Ini Tugas mu ya Atmin</h3>
        <h1 class="heading">Manage Jewelry Collection</h1>
        
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

        <!-- buat tambah produk baru -->
        <div style="text-align: right; margin-bottom: 1rem;">
            <button class="btn add-btn" onclick="openAddModal()">
                <i class="fas fa-plus"></i> Add New Jewelry
            </button>
        </div>
        <div class="collection-table-container">
            <table class="collection-table">
                <thead>
                    <tr>
                        <th>Image</th>
                        <th>Name</th>
                        <th>Description</th>
                        <th>Price</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $query = "SELECT * FROM collection ORDER BY collection_id DESC";
                    $result = $conn->query($query);
                    if ($result && $result->num_rows > 0):
                        while ($row = $result->fetch_assoc()):
                    ?>
                    <tr>
                        <td><img src="../image/<?php echo htmlspecialchars($row['image']); ?>" width="80" alt=""></td>
                        <td><?php echo htmlspecialchars($row['product_name']); ?></td>
                        <td><?php echo htmlspecialchars(substr($row['description'], 0, 50)) . '...'; ?></td>
                        <td>Rp. <?php echo number_format($row['price'], 0, ',', '.'); ?></td>
                        <td>
                            <button class="btn edit-btn" onclick="openEditModal(
                                '<?php echo $row['collection_id']; ?>',
                                '<?php echo htmlspecialchars($row['product_name'], ENT_QUOTES); ?>',
                                '<?php echo htmlspecialchars($row['description'], ENT_QUOTES); ?>',
                                '<?php echo $row['price']; ?>',
                                '<?php echo htmlspecialchars($row['image']); ?>'
                            )">Edit</button>
                            <button class="btn delete-btn" onclick="confirmDelete('<?php echo $row['collection_id']; ?>')">Delete</button>
                        </td>
                    </tr>
                    <?php
                        endwhile;
                    else:
                    ?>
                    <tr>
                        <td colspan="5" style="text-align: center; padding: 2rem;">Belum ada produk.</td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>

    <!-- modal untuk add Product -->
    <div id="addModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Add New Jewelry</h2>
                <span class="close-btn" onclick="closeAddModal()">&times;</span>
            </div>
            <form action="admin_collection.php" method="POST" enctype="multipart/form-data">
                <div class="input-group">
                    <label for="add_name">Product Name</label>
                    <input type="text" id="add_name" name="name" required>
                </div>
                <div class="input-group">
                    <label for="add_description">Description</label>
                    <textarea id="add_description" name="description" rows="4" required></textarea>
                </div>
                <div class="input-group">
                    <label for="add_price">Price</label>
                    <input type="number" id="add_price" name="price" required>
                </div>
                <div class="input-group">
                    <label for="add_image">Product Image</label>
                    <input type="file" id="add_image" name="image" accept="image/*" required>
                </div>
                <div class="modal-footer">
                    <button type="submit" name="add_product" class="btn">Add Product</button>
                    <button type="button" class="btn btn-cancel" onclick="closeAddModal()">Cancel</button>
                </div>
            </form>
        </div>
    </div>
    <!-- modal untuk edit Product -->
    <div id="editModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Edit Jewelry Information</h2>
                <span class="close-btn" onclick="closeEditModal()">&times;</span>
            </div>
            <form action="admin_collection.php" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="product_id" id="edit_product_id">
                <input type="hidden" name="old_image" id="edit_old_image">
                <div class="input-group">
                    <label for="edit_name">Product Name</label>
                    <input type="text" id="edit_name" name="name" required>
                </div>
                <div class="input-group">
                    <label for="edit_description">Description</label>
                    <textarea id="edit_description" name="description" rows="4" required></textarea>
                </div>
                <div class="input-group">
                    <label for="edit_price">Price</label>
                    <input type="number" id="edit_price" name="price" required>
                </div>
                <div class="input-group">
                    <label for="edit_image">Change Image (Leave blank to keep current)</label>
                    <input type="file" id="edit_image" name="image" accept="image/*">
                    <img id="edit_image_preview" src="#" alt="Current Image" style="width: 100px; margin-top: 10px; display:none;">
                </div>
                <div class="modal-footer">
                    <button type="submit" name="update_product" class="btn">Save Changes</button>
                    <button type="button" class="btn btn-cancel" onclick="closeEditModal()">Cancel</button>
                </div>
            </form>
        </div>
    </div>
    <style>
        .manage-collection { 
            padding: 2rem 9%; }

        .collection-table-container { 
            overflow-x: auto; }

        .collection-table { 
            width: 100%; 
            border-collapse: collapse; 
            margin-top: 1rem; 
            background: #fff; 
            box-shadow: var(--box-shadow); }

        .collection-table th, .collection-table td { 
            padding: 12px 15px; 
            border: 1px solid #ddd; 
            text-align: left; 
            font-size: 1.6rem; }

        .collection-table th { 
            background-color: var(--orange); 
            color: #fff; 
            font-weight: bold; }

        .collection-table tbody tr:nth-child(even) { 
            background-color: #f9f9f9; }

        .collection-table tbody tr:hover { 
            background-color: #f1f1f1; }

        .edit-btn { 
            background-color: #007bff; 
            margin-right: 5px; }

        .edit-btn:hover { 
            background-color: #0056b3; }

        .delete-btn { 
            background-color: #dc3545; }

        .delete-btn:hover { 
            background-color: #c82333; }

        .add-btn { 
            background-color: #28a745; 
            color: white; 
            padding: 10px 20px; 
            font-size: 1.6rem; 
            border: none; 
            border-radius: 5px; 
            cursor: pointer; }

        .add-btn:hover { 
            background-color: #218838; }

        .modal { 
            display: none; 
            position: fixed; 
            z-index: 1000; 
            left: 0; top: 0; 
            width: 100%; 
            height: 100%; 
            background-color: rgba(0,0,0,0.5); }

        .modal-content { 
            background-color: #fefefe; 
            margin: 5% auto; 
            padding: 0; 
            border-radius: 8px;
             width: 90%; 
             max-width: 600px; 
             box-shadow: 0 5px 15px rgba(0,0,0,0.3); }

        .modal-header { 
            background-color: var(--orange); 
            color: white; 
            padding: 15px 20px;
             display: flex; 
             justify-content: space-between; 
             align-items: center; 
             border-radius: 8px 8px 0 0; }

        .modal-header h2 { 
            margin: 0; }

        .close-btn { 
            color: white; 
            font-size: 28px; 
            font-weight: bold; 
            cursor: pointer; }

        .close-btn:hover { 
            opacity: 0.7; }

        .modal-content form { 
            padding: 20px; }

        .input-group { 
            margin-bottom: 15px; }

        .input-group label { 
            display: block; 
            margin-bottom: 5px; 
            font-weight: bold; }

        .input-group input, .input-group textarea { 
            width: 100%; 
            padding: 10px; 
            order: 1px solid #ccc;
            border-radius: 4px; 
            box-sizing: border-box; }

        .modal-footer { 
            text-align: right; 
            padding: 10px 20px; 
            background-color: #f1f1f1; 
            border-radius: 0 0 8px 8px; }

        .btn-cancel { 
            background-color: #6c757d; 
            margin-left: 10px; }

        .navbar a.active { 
            color: var(--orange); 
            background: #eee; }
            
    </style>

    <script>
    // Fungsi untuk Modal Add
    function openAddModal() {
        document.getElementById('addModal').style.display = 'block';
    }
    function closeAddModal() {
        document.getElementById('addModal').style.display = 'none';
        document.getElementById('add_name').value = '';
        document.getElementById('add_description').value = '';
        document.getElementById('add_price').value = '';
        document.getElementById('add_image').value = '';
    }

    // Fungsi untuk Modal Edit
    function openEditModal(id, name, description, price, image) {
        document.getElementById('edit_product_id').value = id;
        document.getElementById('edit_name').value = name;
        document.getElementById('edit_description').value = description;
        document.getElementById('edit_price').value = price;
        document.getElementById('edit_old_image').value = image;
        
        // Tampilkan preview gambar lama
        const preview = document.getElementById('edit_image_preview');
        preview.src = '../image/' + image;
        preview.style.display = 'block';
        
        document.getElementById('editModal').style.display = 'block';
    }
    function closeEditModal() {
        document.getElementById('editModal').style.display = 'none';
        document.getElementById('edit_image_preview').style.display = 'none';
    }

    // Fungsi untuk Konfirmasi Delete
    function confirmDelete(productId) {
        if (confirm('Apakah Anda yakin ingin menghapus produk ini? Tindakan ini tidak dapat dibatalkan.')) {
            window.location.href = 'admin_collection.php?delete_id=' + productId;
        }
    }
    </script>
</body>
</html>
<?php $conn->close(); ?>