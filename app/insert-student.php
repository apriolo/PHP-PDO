<?php

use Alura\Pdo\Domain\Model\Student;
use Alura\Pdo\Infrastructure\Persistence\ConnectionCreator;

require_once 'vendor/autoload.php';


$pdo = ConnectionCreator::createConnection();

$student = new Student(
    null,
    'Teste de User',
    new \DateTimeImmutable('1998-10-15')
);

$sqlInsert = "INSERT INTO students (name, birth_date) values (:name, :birth_date);";
$statement = $pdo->prepare($sqlInsert);
$statement->bindValue(':name', $student->name());
$statement->bindValue(':birth_date', $student->birthDate()->format("Y-m-d"));
$response = $statement->execute();

if ($response) {
    echo "Aluno Inserido";
}