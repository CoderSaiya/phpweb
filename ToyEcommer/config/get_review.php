<?php
session_start();

$reviews = [];

if (isset($_GET['order_id'])) {
    $order_id = $_GET['order_id'];
    $query = "SELECT r.product_id, u.username, r.rating, r.comment 
              FROM reviews r
              JOIN products p ON r.product_id = p.product_id
              JOIN users u ON u.user_id = r.user_id
              WHERE r.order_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $order_id);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $reviews[] = $row;
    }

    $stmt->close();
}