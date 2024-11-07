<?php
include "./gnb.php";
if(!$_SESSION['sess_userid']) { //로그인하지 않았다면 로그인 페이지로 이동
	?>
		<script>
			location.replace("index.php");
		</script>
	<?
	exit;
}
if($_SESSION['sess_grade'] != 1) { //관리자 권한확인
	?>
		<script>
			location.replace("index.php");
		</script>
	<?
	exit;
}
?>
<script src="./js/eval_upload.js"></script>
<script src="./js/excel.js"></script>
<section class="content">
    <div class="box box-primary">
        <div class="box-body">
            <div class="col-md-12" style = "margin-left:50px;">
                <div class="sub_title" style="font-size: x-large; font-weight: bold;margin-top:50px;margin-bottom:10px;">[Upload Precautions]</div>
                <div class="form-group">
                    <ol style="padding-left:20px;">
                        <li>
                            <button class="btn btn-success" onclick="download_excel()" type="button">
                                <i class="fa fa-file-excel-o"></i> Excel form for upload
                            </button>
                            <- 업로드 할 경우 업로드용 엑셀을 다운 받아서 작성해주시기 바랍니다.
                        </li>
                        <li>필드의 이름을 임의적으로 수정 혹은 이동을 할 경우 업로드가 불가능 합니다.</li>
                        <li>회색으로 되어있는 셀은 수정이 불가능 합니다.</li>
                        <li>임의로 작업한 문서를 업로드시 오류가 발생합니다.</li>
                    </ol>
                </div>
                <div style="margin-bottom:20px;margin-left:15px;">
                    <form id="uploadForm" enctype="multipart/form-data">
                        <label class="btn btn-primary" for="fileToUpload">파일선택</label>
                        <input type="file" class = "add_files" name="fileToUpload" id="fileToUpload" multiple="multiple" style = "display:none;"accept=".xlsx" />
                            <p id="files_area">
                                <span id="filesList">
                                    <span id="files-names"></span>
                                </span>
                            </p>
                        <input type="button" name="submit" id = "uploadButton" value="업로드" class="btn btn-success">
                    </form>

                    <div id="outputTable"></div>
                    <button id="confirmButton" class="btn btn-success" style="display:none;">최종 업로드</button>
                </div>
            </div>
        </div>
    </div>
</section>
<style>
    input[type=file]::file-selector-button {color:#01324b; width:30%; font-size:14px; font-weight:700; background: #fff; border: 1px solid #01324b;}
    #files-area{width: 30%; margin: 0 auto;}
    .file-block{border-radius: 10px;background-color: rgba(144, 163, 203, 0.2);margin: 5px;color: initial;display: inline-flex;& > span.name{padding-right: 10px;width: max-content;display: inline-flex;}}
    .file-delete{display: flex;width: 24px;color: initial;background-color: #6eb4ff00;font-size: large;justify-content: center;margin-right: 3px;cursor: pointer;&:hover{background-color: rgba(144, 163, 203, 0.2);border-radius: 10px;}& > span{transform: rotate(45deg);}}
</style>
<script>
    function download_excel(){
        location.href="./excel_download.php";
    }

    $(document).ready(function() {
    $("#uploadButton").click(function() {
        var formData = new FormData($("#uploadForm")[0]);

        $.ajax({
            type: "POST",
            url: "phpexcel_upload.php",
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                $("#outputTable").html(response);

                //if font color = red이면 업로드표시X
                var fontcolor = $("#font").css("color");
                if(fontcolor == "rgb(255, 0, 0)"){
                    alert("중복된 데이터가 존재합니다. 데이터 확인 후 다시 파일 업로드 바랍니다.");
                } else {
                    $("#confirmButton").show();
                }
            },
            error: function(xhr, status, error) {
                console.error(xhr.responseText);
            }
        });
    });

    $("#confirmButton").click(function() {
        var formData = new FormData($("#uploadForm")[0]);
        $.ajax({
            type: "POST",
            url: "phpexcel_confirm.php",
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                alert(response);
                location.reload(true);
            },
            error: function(xhr, status, error) {
                console.error(xhr.responseText);
            }
        });
    });
});

    const dt = new DataTransfer();
    $("#fileToUpload").on('change', function(e){
        for(var i = 0; i < this.files.length; i++){
            let fileBloc = $('<span/>', {class: 'file-block'}),
                fileName = $('<span/>', {class: 'name', text: this.files.item(i).name});
            fileBloc.append('<span class="file-delete"><span>+</span></span>')
                .append(fileName);
            $("#filesList > #files-names").append(fileBloc);
        };
        for (let file of this.files) {
            dt.items.add(file);
        }
        this.files = dt.files;

        $('span.file-delete').click(function(){
            let name = $(this).next('span.name').text();
            $(this).parent().remove();
            for(let i = 0; i < dt.items.length; i++){
                if(name === dt.items[i].getAsFile().name){
                    dt.items.remove(i);
                    continue;
                }
            }
            document.getElementById('fileToUpload').files = dt.files;
        });
    });
</script>
