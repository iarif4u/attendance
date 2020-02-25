<?php

require_once 'classConfigInc.php';
require_once 'deviceZklibrary.php';

class Operation
{

    public $connection;

    function __construct()
    {

        $dbmanager = new MyDBConnection;
        $this->connection = $dbmanager->connection("localhost", "school", "admin", "passarif");
        return $this->connection;

    }

    public function get_tm_stamp($date, $timeZone = "Africa/Cairo")
    {
        $format = "d/m/Y";
        $format = str_replace('m', 'MM', $format);
        $format = str_replace('d', 'dd', $format);
        $format = str_replace('Y', 'yyyy', $format);
        $intlDateFormatter = new IntlDateFormatter(
            'en_Us',
            IntlDateFormatter::FULL,
            IntlDateFormatter::FULL,
            $timeZone,
            IntlDateFormatter::GREGORIAN,
            $format
        );
        $intlDateFormatter->setLenient(false);

        return $intlDateFormatter->parse($date);
    }

    public function getWhereNotInData($table, $column, $array)
    {
        $in = str_repeat('?,', count($array) - 1) . '?';
        $sql = "SELECT * FROM $table WHERE $column NOT IN ($in) AND role ='student'";
        $stm = $this->connection->prepare($sql);
        $stm->execute($array);
        return $stm->fetchAll(PDO::FETCH_CLASS);
    }

    public function getConditionData($columns, $table, $conditions)
    {
        $condition = '';
        $values = [];
        if (is_array($conditions)) {
            foreach ($conditions as $column => $data) {
                $condition .= $column . " =?";
                $values[] = $data;
            }
        }
        $columns = implode(",", $columns);
        $sql = "SELECT $columns FROM $table WHERE $condition";
        try {
            $query = $this->connection->prepare($sql);
            $query->execute($values);
            $display = $query->fetchAll(PDO::FETCH_CLASS);
            $counter = $query->rowCount();
        } catch (Exception $exception) {
            return $exception->getMessage();
        }
        if ($counter > 0) {
            return $display;
        } else {
            return false;
        }
    }

    public function getConditionSingleData($columns, $table, $conditions)
    {
        $condition = '';
        $values = [];
        if (is_array($conditions)) {
            foreach ($conditions as $column => $data) {
                $condition .= $column . "? AND ";
                $values[] = $data;
            }
        }
        $condition = rtrim($condition, " AND");
        $columns = (is_array($columns)) ? implode(",", $columns) : $columns;
        try {
            $sql = "SELECT $columns FROM $table WHERE $condition";
            $query = $this->connection->prepare($sql);
            $query->execute($values);
            $display = $query->fetch(PDO::FETCH_ASSOC);
            $counter = $query->rowCount();
        } catch (Exception $exception) {
            return $exception->getMessage();
        }
        if ($counter > 0) {
            return $display;
        } else {
            return false;
        }
    }

    public function insertData($values)
    {
        $table = 'attendance';
        $columns = 'classId,subjectId,date,studentId,status';
        $sql = "INSERT INTO $table($columns) VALUES ($values)";
        $query = $this->connection->prepare($sql);
        $query->execute();
        $counter = $query->rowCount();
        $last_id = $this->connection->lastInsertId();

        if ($counter > 0) {
            return $last_id;
        } else {
            return false;
        }

    }

    public function getAllAttendance($table)
    {
        $sql = "SELECT * FROM $table";
        $query = $this->connection->prepare($sql);
        $query->execute();
        $display = $query->fetchAll(PDO::FETCH_ASSOC);
        $counter = $query->rowCount();
        if ($counter > 0) {
            return $display;
        } else {
            return "The $table is empty";
        }
    }


    public function getSingleDataCondition($columns, $table, $condition, $values)
    {
        $sql = "SELECT $columns FROM $table WHERE $condition ?";
        $Value = array($values);
        $query = $this->connection->prepare($sql);
        $query->execute($Value);
        $display = $query->fetch(PDO::FETCH_ASSOC);
        $counter = $query->rowCount();
        if ($counter > 0) {
            return $display;
        } else {
            return false;
        }
    }


    public function getDataDoubleCondition($columns, $table, $condition1, $values, $condition2, $Value2)
    {
        try {

            $sql = "SELECT $columns FROM $table WHERE $condition1 ? AND $condition2 ?";
            $Value = array($values, $Value2);
            $query = $this->connection->prepare($sql);
            $query->execute($Value);
            $display = $query->fetchALL(PDO::FETCH_ASSOC);
            return $display;
        } catch (Exception $exception) {
            return $exception->getMessage();
        }

    }


    public function displayAttendance()
    {
        $getattendance = $this->getAllAttendance('attendance');

        $show = '';
        $show .= '<table style="width:100%" border="1"> <tr> 
                <th>id</th>
                <th>classId</th>
                <th>subjectId</th>
                <th>date</th>
                <th>studentId</th>
                <th>status</th>
                <th>in_time</th>
                <th>out_time</th>
                <th>attNotes</th>
            </tr>';

        $attendanceLoop = '';
        foreach ($getattendance as $attendance) {
            $attendanceLoop .= '<tr> 
                
                <td>' . $attendance['id'] . '</td>
                <td>' . $attendance['classId'] . '</td>
                <td>' . $attendance['subjectId'] . '</td>
                <td>' . $attendance['date'] . '</td>
                <td>' . $attendance['studentId'] . '</td>
                <td>' . $attendance['status'] . '</td>
                <td>' . $attendance['in_time'] . '</td>
                <td>' . $attendance['out_time'] . '</td>
                <td>' . $attendance['attNotes'] . '</td>
                </tr>';
        }

        $show .= $attendanceLoop;
        $show .= '</table>';
        return $show;
    }

}

$winderWebdata = new Operation();