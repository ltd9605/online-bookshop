<?php
require_once "database.php";

class SubjectsTable
{
    public function getSubjectById($id)
    {
        global $pdo;
        $query = "SELECT * FROM subjects WHERE id = $id";
        $stmt = $pdo->prepare($query);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result;
    }
    public function updateSubjectName($name, $id)
    {
        global $pdo;
        $query = "UPDATE subjects SET subjectName = $name WHERE id = $id";
        $stmt = $pdo->prepare($query);
        $stmt->execute();
    }
}
