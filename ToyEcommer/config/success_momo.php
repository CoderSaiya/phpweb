<?php
include("./connect.php");

if (!isset($_GET['orderId']) || !isset($_GET['resultCode'])) {
    die("Dữ liệu trả về không hợp lệ.");
}

// Lấy các tham số trả về từ MoMo
$orderId = $_GET['orderIdReal'];
$resultCode = $_GET['resultCode'];
$amount = $_GET['amount'];
$message = $_GET['message'];
$signature = $_GET['signature'];

if ($resultCode == 0) {
    $conn->query("START TRANSACTION;");
    try {
        // Cập nhật trạng thái đơn hàng
        $updateOrderSql = $conn->prepare("UPDATE orders SET status = 'paid' WHERE order_id = ?");
        $updateOrderSql->bind_param("i", $orderId);

        if (!$updateOrderSql->execute()) {
            throw new Exception("Không thể cập nhật trạng thái đơn hàng.");
        }

        // Lấy danh sách sản phẩm từ bảng `productorders` liên quan đến đơn hàng
        $getProductsSql = $conn->prepare("SELECT product_id, quantity FROM productorders WHERE order_id = ?");
        $getProductsSql->bind_param("i", $orderId);

        if (!$getProductsSql->execute()) {
            throw new Exception("Không thể lấy thông tin sản phẩm từ đơn hàng.");
        }

        $productResults = $getProductsSql->get_result();

        while ($row = $productResults->fetch_assoc()) {
            $productId = $row['product_id'];
            $quantity = $row['quantity'];

            // Trừ số lượng sản phẩm trong kho
            $updateStockSql = $conn->prepare("UPDATE products SET stock = stock - ? WHERE product_id = ?");
            $updateStockSql->bind_param("ii", $quantity, $productId);

            if (!$updateStockSql->execute()) {
                throw new Exception("Không thể cập nhật số lượng sản phẩm trong kho.");
            }
        }

        $conn->query("COMMIT;");
        echo "Thanh toán thành công và đã lưu thông tin đơn hàng!";
    } catch (Exception $e) {
        $conn->query("ROLLBACK;");
        die("Lỗi khi xử lý dữ liệu: " . $e->getMessage());
    }

    header("Location: ../success_page.php");
    exit;
} else {
    die("Thanh toán không thành công: $message");
}
