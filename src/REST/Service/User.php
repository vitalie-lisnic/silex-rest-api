<?php
/**
 * Created by PhpStorm.
 * User: vitalie
 * Date: 7/22/15
 * Time: 5:40 PM
 */

namespace REST\Service;

use PDO;
use Symfony\Component\Validator\Constraints\Collection;

class User {
    /**
     * @var PDO
     */
    protected $dbConnection;


    public function __construct($dbConnection)
    {
        $this->dbConnection = $dbConnection;
    }

    public function getUserById($userId){
        $stmt = $this->getDbConnection()->prepare("SELECT * FROM users WHERE id=?;");
        $stmt->execute([$userId]);
        $user = $stmt->fetch();

        return $user;
    }

    public function getUserIdsList(){
        $userIds = $this->getDbConnection()->query("SELECT id FROM users;")->fetchAll(PDO::FETCH_FUNC, function($id){
            return $id;
        });

        return $userIds;
    }

    public function createUser($userData){

        $insertFields = implode(',', array_keys($userData));
        $insertValues = implode(',', array_map(function($key){return ":{$key}"; }, array_keys($userData)));

        $stmt = $this->getDbConnection()->prepare(
            "INSERT INTO users ({$insertFields})
             VALUES ({$insertValues})"
        );
        $stmt->execute($userData);

        return $this->getDbConnection()->lastInsertId();
    }

    public function updateUser($userId, $updateUserData){

        $updateQuery = implode(',', array_map(function($field){return "{$field}=:{$field}";}, array_keys($updateUserData)));

        $updateUserData['userId'] = $userId;

        $stmt = $this->getDbConnection()->prepare("UPDATE users SET {$updateQuery} WHERE id=:userId");
        $stmt->execute($updateUserData);

        return $userId;
    }

    public function removeUserById($userId){
        $stmt = $this->getDbConnection()->prepare(
            "DELETE FROM users WHERE id=?"
        );
        $stmt->execute([$userId]);
        return $userId;
    }

    /**
     * @return PDO
     */
    public function getDbConnection()
    {
        return $this->dbConnection;
    }

    /**
     * @param PDO $dbConnection
     */
    public function setDbConnection($dbConnection)
    {
        $this->dbConnection = $dbConnection;
    }

}