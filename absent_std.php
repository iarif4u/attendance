<?php
require 'vendor/autoload.php';
require_once 'Operation.php';

use Carbon\Carbon;

define('TIMEZONE', 'Asia/Dhaka');
$carbon = Carbon::now(TIMEZONE);
$day_of_week = $carbon->dayOfWeek + 1;
$current_hm = $carbon->format('Hi');

$students = execute_absent_api('GET', 'http://localhost/school/api/get');

$student_list = [];
foreach ($students as $student) {
    $student_list[] = $student->studentId;
}
if (count($student_list) == 0) {
    $student_list[] = 0;
};
$student_data = $winderWebdata->getWhereNotInData('users', 'id', $student_list);
$absent_students = [];


foreach ($student_data as $std) {
    $class_id = $std->studentClass;

    //sections
    $section = $winderWebdata->getSingleDataCondition('id', 'sections', 'classId=', $std->studentClass);
    $class_schedule = $winderWebdata->getConditionSingleData("*", "class_schedule", ["dayOfWeek=" => $day_of_week, "sectionId=" => $section['id'], "endTime <=" => $current_hm]);
    if ($class_schedule) {
        $absent_students[] = ['userId' => $std->biometric_id, 'date' => $carbon->format('Y-m-d H:i:s')];;
    }

}
execute_api('POST', 'http://127.0.0.1/school/biometric/0', json_encode(['attendances' => $absent_students]));
dd($absent_students);


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


function execute_absent_api($method, $url, $data = false)
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

    curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

    $result = curl_exec($curl);
    //print_r($result);
    curl_close($curl);

    return json_decode($result);
}

