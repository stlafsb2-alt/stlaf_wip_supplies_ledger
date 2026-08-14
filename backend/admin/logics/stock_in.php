<?php
class StockIn
{
    private $conn;

    public function __construct($conn)
    {
        $this->conn = $conn;
    }

    public function addItem($description, $unit, $unit_price, $supplier, $department, $threshold, $qty_on_hand)
    {
        $stmt = $this->conn->prepare("INSERT INTO items (description, unit, unit_price, supplier, department, threshold, qty_on_hand) VALUES (?, ?, ?, ?, ?, ?, ?)");
        return $stmt->execute([$description, $unit, $unit_price, $supplier, $department, $threshold, $qty_on_hand]);
    }

    public function addStockIn($item_id, $qty_in, $remarks, $supplier, $stock_date)
    {
        if ($qty_in <= 0) return false;

        $stmt = $this->conn->prepare("INSERT INTO stock_in (item_id, qty_in, remarks) VALUES (?, ?, ?)");
        $insert = $stmt->execute([$item_id, $qty_in, $remarks]);

        if (!$insert) return false;

        $stmt2 = $this->conn->prepare("UPDATE items SET qty_on_hand = qty_on_hand + ?, last_stock_added = ?, supplier = ?, created_at = ? WHERE id = ?");

        return $stmt2->execute([$qty_in, $qty_in, $supplier, $stock_date, $item_id]);
    }

    public function getItems()
    {
        $sql = "SELECT id, description, qty_on_hand, unit FROM items ORDER BY description ASC";
        return $this->conn->query($sql);
    }
}
