<?php


class Users
{
    public static function getUsers()
    {
        $pdo = getPDO();

        $statement = $pdo->prepare('SELECT * FROM users');
        $statement->execute();
        $users = $statement->fetchAll(PDO::FETCH_ASSOC);
        $pdo = null;
        return $users;
    }

}