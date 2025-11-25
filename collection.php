<?php
include 'koneksi.php'; // Menghubungkan file ini dengan koneksi ke database 
session_name("customer_session"); // Menentukan nama sesi agar sesuai dengan sesi customer
session_start(); // Memulai sesi agar bisa menyimpan dan mengakses data seperti keranjang belanja

if (!isset($_SESSION['cart'])) { // Mengecek apakah variabel session 'cart' sudah ada
    $_SESSION['cart'] = []; // Jika belum ada, inisialisasi keranjang sebagai array kosong
}

if (isset($_POST['add_to_cart'])) { // Mengecek apakah tombol 'add_to_cart' diklik
    $id = (int)$_POST['id']; // Mengambil ID produk dari form dan mengonversinya ke integer
    $name = htmlspecialchars($_POST['name']); // Mengambil nama produk dan mengamankannya dari karakter berbahaya
    $price = (float)$_POST['price']; // Mengambil harga produk dan mengonversinya ke tipe float
    $image = htmlspecialchars($_POST['image']); // Mengambil path atau URL gambar produk dan mengamankannya

    $existing_item_index = -1; // Variabel untuk menandai apakah produk sudah ada di keranjang
    foreach ($_SESSION['cart'] as $index => $item) { // Melakukan iterasi pada semua item dalam keranjang
        if ($item['id'] == $id) { // Jika ID produk yang akan ditambahkan sama dengan produk di keranjang
            $existing_item_index = $index; // Simpan indeks item tersebut
            break; // Hentikan perulangan karena item sudah ditemukan
        }
    }

    if ($existing_item_index !== -1) { // Jika produk sudah ada di keranjang
        $_SESSION['cart'][$existing_item_index]['quantity'] += 1; // Tambahkan jumlah (quantity) produk sebanyak 1
    } else { // Jika produk belum ada di keranjang
        $_SESSION['cart'][] = [ // Tambahkan produk baru ke keranjang dengan data berikut
            'id' => $id, // ID produk
            'name' => $name, // Nama produk
            'price' => $price, // Harga produk
            'image' => $image, // Gambar produk
            'quantity' => 1 // Jumlah awal produk yang dimasukkan
        ];
    }

    header("Location: collection.php"); // Setelah menambahkan ke keranjang, kembali ke halaman koleksi
    exit; // Hentikan eksekusi kode setelah redirect
}

if (isset($_GET['delete']) && isset($_GET['id'])) { // Mengecek apakah ada permintaan untuk menghapus produk dari keranjang
    $id_to_delete = (int)$_GET['id']; // Mengambil ID produk yang akan dihapus dan memastikan tipenya integer
    
    $_SESSION['cart'] = array_filter($_SESSION['cart'], function($item) use ($id_to_delete) { // Menghapus produk berdasarkan ID
        return $item['id'] != $id_to_delete; // Hanya simpan item yang ID-nya tidak sama dengan produk yang ingin dihapus
    });
    
    $_SESSION['cart'] = array_values($_SESSION['cart']); // Mengatur ulang indeks array agar berurutan kembali
    
    header("Location: collection.php?cart=open"); // Redirect kembali ke halaman koleksi dan buka tampilan keranjang
    exit; // Hentikan eksekusi kode setelah redirect
}

$showCart = isset($_GET['cart']) && $_GET['cart'] == 'open'; // Mengecek apakah parameter 'cart=open' dikirim, untuk menampilkan keranjang
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>nahecididi jewelry</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css"/>
    <link rel="stylesheet" href="https://unpkg.com/boxicons@latest/css/boxicons.min.css">
    <link rel="stylesheet" href="css/collection.css">
    <link rel="stylesheet" href="css/cart.css">
    <style>
        .cart-overlay {  
            display: none; 
            position: fixed; 
            top: 0; left: 0; 
            width: 100%; 
            height: 100%; 
            background: rgba(0, 0, 0, 0.7); 
            justify-content: center; 
            align-items: center; 
            z-index: 1000; }

        .cart-overlay.active { 
            display: flex; }

        .cart-content { 
            background: white; 
            padding: 20px; 
            border-radius: 10px; 
            width: 90%; 
            max-width: 600px;
             max-height: 80vh; 
             overflow-y: auto; }
        .cart-item { 
            display: flex; 
            align-items: center; 
            justify-content: space-between; 
            margin-bottom: 15px; 
            border-bottom: 1px solid #eee; 
            padding-bottom: 10px; }

        .cart-item img { 
            width: 60px; 
            height: 60px; 
            object-fit: cover; 
            margin-right: 15px; }
        .cart-item-details { 
            flex-grow: 1; }

        .cart-item h4 { 
            margin: 0 0 5px 0; }

        .cart-item p { 
            margin: 0; color: #555; }

        .cart-total { 
            text-align: right; 
            font-size: 1.2em; 
            font-weight: bold; 
            margin-top: 20px; 
            border-top: 2px solid #333; 
            padding-top: 10px; }
            
        .close-cart-btn { 
            text-align: center;
            display: block; 
            width: 100%; 
            margin-top: 15px; }
    </style>
</head>
<body>
    <header>
        <a href="mainpage.php" class="Logo">nahecididi jewelry</a>
        <nav class="navbar">
            <a href="mainpage.php">Home</a>
            <a href="#about">About</a>
            <a href="collection.php">Collection</a>
            <a href="#review">Location</a>
            <a href="#order">Book</a>            
        </nav>
        <div class="icons">
            <i class="fas fa-bars" id="menu-bars"></i>
            <a href="collection.php?cart=open" class="bx bx-cart" title="View Cart"></a>
            <a href="logout.php" class="bx bx-log-out" title="Logout"></a>
        </div>
    </header>
    <br><br><br><br>
    
    <section class="collection" id="collection">
        <h3 class="sub-heading">Our Jewelry</h3>
        <h1 class="heading">Popular Jewelry</h1>
        <div class="box-container">
            <?php

            $query = "SELECT * FROM collection";
            $result = mysqli_query($conn, $query);
            if ($result && mysqli_num_rows($result) > 0) {
                while ($row = mysqli_fetch_assoc($result)) {
                    ?>
                    <div class="box">
                        <div class="image">
                            <img src="image/<?php echo htmlspecialchars($row['image']); ?>" alt="<?php echo htmlspecialchars($row['product_name']); ?>">
                        </div>
                        <div class="content">
                            <h3><?php echo htmlspecialchars($row['product_name']); ?></h3>
                            <p><?php echo htmlspecialchars($row['description'] ?? ''); ?></p>
                            <span class="price">Rp. <?php echo number_format($row['price'], 0, ',', '.'); ?></span>
                            <form method="POST" action="collection.php">
                                <input type="hidden" name="id" value="<?php echo (int)$row['collection_id']; ?>">
                                <input type="hidden" name="name" value="<?php echo htmlspecialchars($row['product_name']); ?>">
                                <input type="hidden" name="price" value="<?php echo (float)$row['price']; ?>">
                                <input type="hidden" name="image" value="<?php echo htmlspecialchars($row['image']); ?>">
                                <input type="hidden" name="add_to_cart" value="1">
                                <button type="submit" class="btn">Add to Cart</button>
                            </form>
                        </div>
                    </div>
                    <?php
                }
            } else {
                echo "<p class='no-data'>No jewelry available at the moment.</p>";
            }
            ?>
        </div>
    </section>

    <!-- --- OVERLAY KERANJANG --- -->
    <div class="<?php echo $showCart ? 'cart-overlay active' : 'cart-overlay'; ?>">
        <div class="cart-content">
            <h2>Your Cart</h2>
            <div id="cart-items">
                <?php if (!empty($_SESSION['cart'])): ?>
                    <?php foreach ($_SESSION['cart'] as $item): ?>
                        <div class="cart-item">
                            <img src="image/<?php echo htmlspecialchars($item['image']); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>">
                            <div class="cart-item-details">
                                <h4><?php echo htmlspecialchars($item['name']); ?></h4>
                                <p>Qty: <?php echo (int)$item['quantity']; ?></p>
                            </div>
                            <span>Rp. <?php echo number_format($item['price'] * $item['quantity'], 0, ',', '.'); ?></span>
                            <form method="GET" action="collection.php" style="display:inline; margin-left: 10px;">
                                <input type="hidden" name="delete" value="1">
                                <input type="hidden" name="id" value="<?php echo (int)$item['id']; ?>">
                                <button type="submit" class="btn" style="background:red; padding: 5px 10px;">X</button>
                            </form>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p>Keranjang kosong.</p>
                <?php endif; ?>
            </div>
            <?php if (!empty($_SESSION['cart'])): ?>
                <div class="cart-total">
                    Total: Rp <?php
                        $total = 0;
                        foreach ($_SESSION['cart'] as $item) {
                            $total += $item['price'] * $item['quantity'];
                        }
                        echo number_format($total, 0, ',', '.');
                    ?>
                </div>
                <!-- Form untuk melakukan checkout -->
                <form method="POST" action="cart/checkout.php">
                    <input type="hidden" name="checkout" value="1">
                    <button type="submit" class="btn checkout-btn">Checkout</button>
                </form>
            <?php endif; ?>
            <a href="collection.php" class="btn close-cart-btn">Close</a>
        </div>
    </div>
</body>
</html>