<?php

$pathSqlite = __DIR__ . "/banco.sqlite";
$pdo = new PDO('sqlite:' . $pathSqlite);

echo 'Connection';

$pdo->exec("INSERT INTO phone (
                        area_code, 
                        number, 
                        student_id
                    ) 
                    values ('14','991030869',1),('14','974008899',1);");
exit();

$createTable = "CREATE TABLE IF NOT EXISTS students (
                    id INTEGER PRIMARY KEY, 
                    name TEXT, 
                    birth_date TEXT
                );
                CREATE TABLE IF NOT EXISTS phone (
                    id INTEGER PRIMARY KEY,
                    area_code TEXT,
                    number TEXT,
                    student_id INTEGER,
                    FOREIGN KEY (student_id) REFERENCES students(id)
                );";

$pdo->exec($createTable);