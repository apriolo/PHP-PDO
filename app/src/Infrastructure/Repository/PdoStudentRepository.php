<?php


namespace Alura\Pdo\Infrastructure\Repository;


use Alura\Pdo\Domain\Model\Phone;
use Alura\Pdo\Domain\Model\Student;
use Alura\Pdo\Domain\Repository\StudentRepository;
use Alura\Pdo\Infrastructure\Persistence\ConnectionCreator;
use PDO;

class PdoStudentRepository implements StudentRepository
{

    private PDO $connection;

    public function __construct(PDO $connection)
    {
        $this->connection = $connection;
    }

    public function allStudents(): array
    {
        $stmt = $this->connection->query('SELECT * FROM students');
        return $this->hydrateStudentList($stmt);
    }

    public function studentsBirthAt(\DateTimeInterface $birthDate): array
    {
        $stmt = $this->connection->prepare('SELECT * FROM students WHERE birth_date = ?');
        $stmt->bindValue(1, $birthDate->format('Y-m-d'));
        $stmt->execute();

        return  $this->hydrateStudentList($stmt);
    }

    public function save(Student $student)
    {
        if (is_null($student->id())) {
            return $this->insert($student);
        }
        return $this->update($student);
    }

    public function remove(Student $student)
    {
        $prepare = $this->connection->prepare('DELETE FROM students WHERE id = ?');
        $prepare->bindValue(1, $student->id(), PDO::PARAM_INT);
        return $prepare->execute();
    }

    private function insert(Student $student): bool
    {
        $prepare = $this->connection->prepare("INSERT INTO students (name, birth_date) values (?, ?);");
        $prepare->bindValue('1', $student->name());
        $prepare->bindValue('2', $student->birthDate()->format("Y-m-d"));
        $response = $prepare->execute();
        if ($response) {
            $student->defineId($this->connection->lastInsertId());
        }
        return $response;
    }

    private function update(Student $student): bool
    {
        $prepare = $this->connection->prepare("UPDATE students SET name = ?, birth_date =  ? WHERE id = ?;");
        $prepare->bindValue('1', $student->name());
        $prepare->bindValue('2', $student->birthDate()->format("Y-m-d"));
        $prepare->bindValue(3, $student->id(), PDO::PARAM_INT);
        return $prepare->execute();
    }

    private function hydrateStudentList(\PDOStatement $stmt): array
    {
        $studentDataList = $stmt->fetchAll();
        $studentList = [];

        foreach ($studentDataList as $studentData) {
            $studentList[] =  new Student(
                $studentData['id'],
                $studentData['name'],
                new \DateTimeImmutable($studentData['birth_date'])
            );
        }
        return $studentList;
    }

    private function fillPhonesOf(Student $student): void
    {
        $sqlQuery = 'SELECT id, area_code, number FROM phone WHERE student_id = ?';
        $stmt = $this->connection->prepare($sqlQuery);
        $stmt->bindValue(1, $student->id(), PDO::PARAM_INT);
        $stmt->execute();

        $phoneDataList = $stmt->fetchAll();

        foreach ($phoneDataList as $phone) {
            $phone = new Phone(
                $phone['id'],
                $phone['area_code'],
                $phone['number']
            );
            $student->addPhone($phone);
        }
    }

    public function studentsWithPhones(): array
    {
        $sql = "SELECT students.id,
                        students.nome, 
                        students.birth_date, 
                        phone.id as phone_id,
                        phone.area_code,
                        phone.number
                FROM students 
                JOIN phone ON students.id = phone.students_id;";
        $stmt = $this->connection->query($sql);
        $result = $stmt->fetchAll();

        $studentList = [];
        foreach ($result as $row) {
            if (!array_key_exists($row['id'], $studentList)) {
                $studentList[$row['id']] = new Student(
                    $row['id'],
                    $row['name'],
                    new \DateTimeImmutable($row['birth_date'])
                );
            }
            $phone = new Phone($row['phone_id'], $row['area_code'], $row['number']);
            $studentList[$row['id']]->addPhone($phone);
        }

        return $studentList;
    }
}