<?php

use Alura\Pdo\Domain\Model\Student;
use Alura\Pdo\Infrastructure\Persistence\ConnectionCreator;
use Alura\Pdo\Infrastructure\Repository\PdoStudentRepository;

require_once __DIR__ . '/vendor/autoload.php';

// Crio uma conexao e crio um repositorio
$connection = ConnectionCreator::createConnection();
$repository = new PdoStudentRepository($connection);

// transictions, só insere de fato caso não ocorra erros
$connection->beginTransaction();

try {
    // Croi um objeto studant
    $student = new Student(
        null,
        'Teste transiction',
        new DateTimeImmutable('1998-07-14')
    );
    // Salvo o studant
    $repository->save($student);

    $student2 = new Student(
        null,
        'Teste transiction SStudent 2',
        new DateTimeImmutable('1998-07-14')
    );
    $repository->save($student2);

    // Insere de fato as informações
    $connection->commit();
} catch (\PDOException $e)
{
    // Não insere e volta ao ponto de inicio
    echo $e->getMessage();
    $connection->rollBack();
}