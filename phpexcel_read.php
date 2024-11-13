<?php
  include_once "./include/class/PHPExcel-1.8/PHPExcel.php";
  include_once "./include/class/PHPExcel-1.8/PHPExcel/IOFactory.php";
  include_once "./conndb.php";
  
  //데이터베이스 연결
  try {
    $conn = new mysqli($DB_HOST, $DB_USER, $DB_PASSWD, $DB_SNAME, $db_port);
  if ($conn->connect_error) {
    die('데이터베이스 연결 실패: ' . $conn->connect_error);
  }
  } catch (Exception $e) {
    echo '데이터베이스 연결 오류: ' . $e->getMessage();
  }

  //엑셀파일 업로드 처리
  $uploadedFile = $_FILES['fileToUpload']['tmp_name'];

  try {
    //엑셀파일 읽기 및 데이터 가져오기
    $excelReader = PHPExcel_IOFactory::createReaderForFile($uploadedFile);
    $excelObj = $excelReader->load($uploadedFile);
    $worksheet = $excelObj->getActiveSheet();
    //행과 열 정보 추출
    $highestRow = $worksheet->getHighestRow();
    $highestColumn = $worksheet->getHighestColumn();
    $highestColumnIndex = PHPExcel_Cell::columnIndexFromString($highestColumn);
    $columnNames = array(); // 엑셀 파일의 컬럼 이름을 저장할 배열
    
    $excelData = array();
    $duplicates = array();

    //엑셀 데이터 읽기
    for ($row = 1; $row <= $highestRow; $row++) {
      $rowData = array();
      for ($col = 0; $col < $highestColumnIndex; $col++) {
        $cellValue = $worksheet->getCellByColumnAndRow($col, $row)->getValue();
        $rowData[] = $cellValue;
      }
      $excelData[] = $rowData;
    }

    //중복데이터 확인
    foreach ($excelData as $rowIndex => $row) {
      $user_id = $row[1];
      $year = $row[2];
      $half = $row[3];
      
      $sql = "SELECT * FROM table_name WHERE user_id = '$user_id' AND year = '$year' AND half = '$half'";
      $result = mysqli_query($conn, $sql);
      
      if ($result->num_rows > 0) {
        // 중복된 데이터가 있다면 해당 셀의 정보를 저장
        $duplicates[] = array(
        'rowIndex' => $rowIndex+1
        );
      }
    }
    
    // 엑셀 파일의 첫 행(컬럼 이름)을 읽어와서 $columnNames[]에 저장
    for ($col = 0; $col < $highestColumnIndex; $col++) {
      $columnNames[] = $worksheet->getCellByColumnAndRow($col, 1)->getValue();
    }
    
    for ($row = 2; $row <= $highestRow; $row++) { //둘째 행부터 값 탐색 
      $rowData = array();
      for ($col = 0; $col < $highestColumnIndex; $col++) {
        $rowData[$columnNames[$col]] = $worksheet->getCellByColumnAndRow($col, $row)->getValue();
      }

      //HTML 테이블 출력
      $output = '<table border="1" style="width:100%;margin-bottom:20px;margin-top:20px;text-align:center;">';
      for ($row = 1; $row <= $highestRow; $row++) {
        $output .= '<tr';
        if (in_array(['rowIndex' => $row], $duplicates)) { //중복값 있다면 red color로 표시
          $output .= ' style="color: red;" id = "font"';
        }
        $output .= '>';
        for ($col = 0; $col < $highestColumnIndex; $col++) {
          $output .= '<td>' . $worksheet->getCellByColumnAndRow($col, $row)->getValue() . '</td>';
        }
        $output .= '</tr>';
      }
    }
    $output .= '</table>';
    echo $output;
  
  } catch (Exception $e) {
    echo '파일을 읽는 도중 오류 발생: ', $e->getMessage();
  }
?>
