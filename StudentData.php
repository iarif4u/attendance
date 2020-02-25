<?php

require 'vendor/autoload.php';
require_once 'classConfigInc.php';
require_once 'deviceZklibrary.php';
require_once 'Operation.php';

use Carbon\Carbon;

define('TIMEZONE', 'Asia/Dhaka');

$winderDeviecData = new ZKLibrary('192.168.0.80', 4370);
$winderDeviecData->connect();
$winderAttendance = $winderDeviecData->getAttendance();

$prev_date = date(strtotime(date('Y-m-d') . ' -1 day'));

$prev_day = Carbon::createFromTimestamp($prev_date);

$users = $winderWebdata->getConditionData(['id', 'fullName', 'biometric_id', 'username', 'role'], 'users', ['activated' => 1]);
$student_bio_id = [];
foreach ($users as $user) {
    $student_bio_id[$user->biometric_id] = $user->id;
}

$attendance = [];
$students = [];
foreach ($winderAttendance as $value) {
    $date = date("d/m/Y", strtotime($value[3]));
    if ($prev_day->diffInDays(Carbon::createFromTimeString($value[3])) < 3) {
        if (!in_array($date . $value[1], $students)) {
            $loopwinderGetSingleAttendance = $winderWebdata->getDataDoubleCondition("*", "attendance", "studentId=", $student_bio_id[$value[1]], "date =", $winderWebdata->get_tm_stamp($date, "Asia/Dhaka"));
            if (count($loopwinderGetSingleAttendance) == 0) {
                $attendance[] = ['userId' => $value[1], 'date' => $value[3]];
                $students[] = $date . $value[1];
            }
        }
    }
}

//$attendance[] = ['userId' => 5, 'date' => date('Y-m-d H:i:s')];
execute_api('POST', 'http://127.0.0.1/school/biometric', json_encode(['attendances' => $attendance]));


function execute_api($method, $url, $data = false)
{
    $curl = curl_init();

    switch ($method) {
        case "POST":
            curl_setopt($curl, CURLOPT_POST, 1);

            if ($data)
                curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
            break;
        case "PUT":
            curl_setopt($curl, CURLOPT_PUT, 1);
            break;
        default:
            if ($data)
                $url = sprintf("%s?%s", $url, http_build_query($data));
    }

    // Optional Authentication:
    // curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
    //curl_setopt($curl, CURLOPT_USERPWD, "username:password");
    curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

    $result = curl_exec($curl);
    print_r($result);
    curl_close($curl);

    return $result;
}

