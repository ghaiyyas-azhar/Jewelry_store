<?php
// Aktifkan session
session_start();

// Cek jika request adalah POST dan ada aksi 'add_to_cart'
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_to_cart'])) {
    
    // Inisialisasi cart jika belum ada
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }

    // Ambil data dari POST request
    $id = (int)$_POST['id'];
    $name = htmlspecialchars($_POST['name']);
    $price = (float)$_POST['price'];
    $image = htmlspecialchars($_POST['image']);

    $existing_item_index = -1;
    // Cek apakah produk sudah ada di cart
    foreach ($_SESSION['cart'] as $index => $item) {
        if ($item['id'] == $id) {
            $existing_item_index = $index;
            break;
        }
    }

    if ($existing_item_index !== -1) {
        // Jika produk sudah ada, tambahkan quantity
        $_SESSION['cart'][$existing_item_index]['quantity'] += 1;
    } else {
        // Jika produk baru, tambahkan ke cart
        $_SESSION['cart'][] = [
            'id' => $id,
            'name' => $name,
            'price' => $price,
            'image' => $image,
            'quantity' => 1
        ];
    }

    // Set header untuk response JSON
    header('Content-Type: application/json');
    // Kirim response sukses
    echo json_encode(['status' => 'success', 'message' => 'Item berhasil ditambahkan ke keranjang!']);

} else {
    // Jika bukan request yang valid, kirim response error
    header('Content-Type: application/json');
    echo json_encode(['status' => 'error', 'message' => 'Request tidak valid.']);
}
?>