<?php
require_once("./config/connect.php");

// Lấy dữ liệu từ MoMo
$data = json_decode(file_get_contents('php://input'), true);

$orderId = $data['orderId'];
$resultCode = $data['resultCode'];

if ($resultCode == 0) {
    // Thanh toán thành công
    $stmt = $conn->prepare("UPDATE orders SET status = 'paid' WHERE order_id = ?");
    $stmt->bind_param("i", $orderId);
    $stmt->execute();
} else {
    // Thanh toán thất bại, rollback giao dịch
    $conn->query("START TRANSACTION;");

    try {
        // Xóa đơn hàng
        $deleteOrderSql = $conn->prepare("DELETE FROM orders WHERE order_id = ?");
        $deleteOrderSql->bind_param("i", $orderId);
        $deleteOrderSql->execute();

        // Khôi phục tồn kho sản phẩm
        $productOrdersSql = $conn->prepare("SELECT product_id, quantity FROM productorders WHERE order_id = ?");
        $productOrdersSql->bind_param("i", $orderId);
        $productOrdersSql->execute();
        $productOrders = $productOrdersSql->get_result();

        while ($product = $productOrders->fetch_assoc()) {
            $updateStockSql = $conn->prepare("UPDATE products SET stock = stock + ? WHERE product_id = ?");
            $updateStockSql->bind_param("ii", $product['quantity'], $product['product_id']);
            $updateStockSql->execute();
        }

        // Xóa chi tiết đơn hàng
        $deleteProductOrdersSql = $conn->prepare("DELETE FROM productorders WHERE order_id = ?");
        $deleteProductOrdersSql->bind_param("i", $orderId);
        $deleteProductOrdersSql->execute();

        $conn->query("COMMIT;");
    } catch (Exception $e) {
        $conn->query("ROLLBACK;");
        die("Lỗi rollback giao dịch: " . $e->getMessage());
    }
}

http_response_code(200);
