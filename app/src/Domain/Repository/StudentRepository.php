<?php

namespace Alura\Pdo\Domain\Repository;

use Alura\Pdo\Domain\Model\Student;

interface StudentRepository
{
    public function allStudents();

    public function studentsBirthAt(\DateTimeInterface $birthDate): array;

    public function save(Student $student);

    public function remove(Student $student);

    public function studentsWithPhones(): array;
}