<?php

class Users
{
    /*
     * ф-я для получения всех менеджеров из БД (если не передан id)
     * если передан id заказа - возвращает только одного менеджера
     * возвращает менеджеров (менеджера) в том виде в котором они хранятся в таблице БД
     */
    public static function getUsers($id = null)
    {
        $query = 'SELECT * FROM users';

        if ($id != null) {
            $query .= ' WHERE id =' . $id;
        }
        $pdo = Helper::getPDO();

        $statement = $pdo->prepare($query);
        $statement->execute();
        $users = $statement->fetchAll(PDO::FETCH_ASSOC);

        $pdo = null;
        return $users;
    }
}