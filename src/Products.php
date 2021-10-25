<?php

class Products
{
    public static function getProducts($id = null)
    {
        $pdo = getPDO();
        $query = 'SELECT * FROM products';
        if ($id != null) {
            $query .= ' WHERE id =' . $id;
        }
        $statement = $pdo->prepare($query);
        $statement->execute();
        $products = $statement->fetchAll(PDO::FETCH_ASSOC);
        $pdo = null;
        return $products;
    }

    public static function decreaseStock($id, $count)
    {
        $pdo = getPDO();
        $statement = $pdo->prepare('UPDATE products SET stock = stock-? WHERE id = ?');
        $statement->execute([$count, $id]);
        $pdo = null;

        return $statement->errorCode();
    }
    public static function increaseStock($id, $count)
    {
        $pdo = getPDO();
        $statement = $pdo->prepare('UPDATE products SET stock = stock + ? WHERE id = ?');
        $statement->execute([$count, $id]);
        $pdo = null;

        return $statement->errorCode();
    }

}