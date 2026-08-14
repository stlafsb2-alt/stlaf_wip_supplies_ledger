<?php
class Item
{
    private $conn;

    public function __construct($conn)
    {
        $this->conn = $conn;
    }

    public function addItem($description, $unit, $unit_price, $supplier, $department, $threshold, $date_added)
    {
        if ($unit_price < 0) { $unit_price = 0; }
        if ($threshold < 0) { $threshold = 0; }

        $stmt = $this->conn->prepare("INSERT INTO items (description, unit, unit_price, supplier, department, threshold, created_at) VALUES (?, ?, ?, ?, ?, ?, ?)");
        return $stmt->execute([$description, $unit, $unit_price, $supplier, $department, $threshold, $date_added]);
    }

    public function getAllItems()
    {
        $sql = "SELECT id, description, unit, qty_on_hand, threshold, created_at 
                FROM items 
                WHERE is_archived = false
                ORDER BY description ASC";
        return $this->conn->query($sql);
    }

    public function archiveItem($id)
    {
        $stmt = $this->conn->prepare("UPDATE items SET is_archived = true WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function restoreItem($id)
    {
        $stmt = $this->conn->prepare("UPDATE items SET is_archived = false WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function deleteItem($id)
    {
        $stmt = $this->conn->prepare("DELETE FROM items WHERE id = ?");
        return $stmt->execute([$id]);
    }
}

?>