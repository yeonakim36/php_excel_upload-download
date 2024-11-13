<?php
include_once "include/class/PHPExcel-1.8/PHPExcel/IOFactory.php";
include_once "./conndb.php";

try {
    $conn = new mysqli($DB_HOST, $DB_USER, $DB_PASSWD, $DB_SNAME, $db_port);
    if ($conn->connect_error) {
        die('데이터베이스 연결 실패: ' . $conn->connect_error);
    }
} catch (Exception $e) {
    echo '데이터베이스 연결 오류: ' . $e->getMessage();
}

$uploadedFile = $_FILES['fileToUpload']['tmp_name'];

try {
    $excelReader = PHPExcel_IOFactory::createReaderForFile($uploadedFile);
    $excelObj = $excelReader->load($uploadedFile);
    $worksheet = $excelObj->getActiveSheet();
    $highestRow = $worksheet->getHighestRow();
    $highestColumn = $worksheet->getHighestColumn();
    $highestColumnIndex = PHPExcel_Cell::columnIndexFromString($highestColumn);

    $columnNames = array();  // 엑셀 파일의 컬럼 이름을 저장할 배열

    // 엑셀 파일의 첫 행(컬럼 이름)을 읽어옴
    for ($col = 0; $col < $highestColumnIndex; $col++) {
        $columnNames[] = $worksheet->getCellByColumnAndRow($col, 1)->getValue();
    }

    // 데이터베이스에 저장 또는 업데이트
    for ($row = 2; $row <= $highestRow; $row++) {
        $rowData = array();

        // 각 행의 데이터를 읽어옴, $c1~는 엑셀컬럼맞춰서 수정하기
        for ($col = 0; $col < $highestColumnIndex; $col++) {
            $rowData[$columnNames[$col]] = $worksheet->getCellByColumnAndRow($col, $row)->getValue();
        }
        $c0 = $rowData[$columnNames[0]]; //user_no
        $c1 = $rowData[$columnNames[1]]; //user_id
        $c2 = $rowData[$columnNames[2]]; //year
        $c3 = $rowData[$columnNames[3]]; //half
        $c4 = $rowData[$columnNames[4]]; //total_grade
        $c5 = $rowData[$columnNames[5]]; //score
        $c6 = $rowData[$columnNames[6]]; //comment

        // 년, 분기, 계정 일치 시 업데이트 로직 짜야함
        $sql = "INSERT IGNORE INTO table_name (user_no, user_id, year, half, total_grade, score, comment)
                VALUES ('$c0', '$c1', '$c2', '$c3', '$c4', '$c5', '$c6')"; //user_no, user_id, year, half - unique key로 중복된다면 추가안되도록 설정
                // ON DUPLICATE KEY UPDATE total_grade = VALUES(total_grade), score = VALUES(score), comment = VALUES(comment)"; // 중복된 상황에 대한 또다른 처리
        mysqli_query($conn, $sql);
    }
    echo '데이터가 성공적으로 업로드되었습니다.';

} catch (Exception $e) {
    echo '파일을 읽는 도중 오류 발생: ', $e->getMessage();
}

$conn->close();

?>
