<?php

class Products
{
    /*
     * ф-я для получения всех товаров из БД (если не передан id)
     * если передан id заказа - возвращает только этот товар
     * возвращает товар (товары) в том виде в котором они хранятся в таблице БД
     */
    public static function getProducts($id = null)
    {
        $pdo = Helper::getPDO();
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
    /*
     * ф-я уменьшения остатков
     * принимает массив товаров
     * возвращает код ошибки
     */
    public static function decreaseStock($orderItems)
    {
        $pdo = Helper::getPDO();

        foreach ($orderItems as $key => $value) {
            $statement = $pdo->prepare('UPDATE products SET stock = stock-? WHERE id = ?');
            $statement->execute([intval($value['count']), $value['id']]);
        }
        $pdo = null;

        return $statement->errorCode();
    }
    /*
     * ф-я увеличения остатков
     * принимает массив товаров
     * возвращает код ошибки
     */
    public static function increaseStock($orderItems)
    {
        $pdo = Helper::getPDO();
        foreach ($orderItems as $key => $value) {
            $statement = $pdo->prepare('UPDATE products SET stock = stock + ? WHERE id = ?');
            $statement->execute([$value['count'], $value['id']]);
        }
        $pdo = null;

        return $statement->errorCode();
    }
}