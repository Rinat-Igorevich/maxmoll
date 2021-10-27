<?php

class Users
{
    public static function getUsers($id = null)
    {
        $query = 'SELECT * FROM users';

        if ($id != null) {
            $query .= ' WHERE id =' . $id;
        }

        $pdo = getPDO();

        $statement = $pdo->prepare($query);
        $statement->execute();
        $users = $statement->fetchAll(PDO::FETCH_ASSOC);

        $pdo = null;
        return $users;
    }
}