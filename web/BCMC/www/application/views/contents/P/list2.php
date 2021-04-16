<?php
//print_r($user);
?>
<section class="card">
        <header class="card-header">
           <!-- <div class="card-actions">
                <a href="#" class="card-action card-action-toggle" data-card-toggle></a>
                <a href="#" class="card-action card-action-dismiss" data-card-dismiss></a>
            </div>-->
            <div calss="card"></div>

            <h2 class="card-title">컨텐츠리스트<button type="button" class="mb-1 mt-1 mr-1 btn btn-primary contents_form_btn" style="float:right">컨텐츠등록</button></h2>
        </header>
        <div class="card-body">
            <table class="table table-responsive-md table-striped mb-0 text-center">
                <thead>

                <tr>
                    <th style="width:5%">#</th>
                    <th style="width:60%">제목</th>
                    <th style="width:10%">용량</th>
                    <th style="width:15%">분류</th>
                    <th style="width:10%">패키징상태</th>
                </tr>
                </thead>
                <tbody class="list_area">
                </tbody>
            </table>
            <div id="ajaxpaging"></div>

    </section>

    <div id="modalAnim" class="zoom-anim-dialog modal-block modal-block-primary mfp-hide" >
        <section class="card" style="background:#ccc;width:800px;">
            <div class="loading-overlay-showing data-loading" style="background-color: rgb(253, 253, 253); border-radius: 0px 0px 5px 5px;"><div class="bounce-loader"><div class="bounce1"></div><div class="bounce2"></div><div class="bounce3"></div></div></div>
            <header class="card-header">
                <h2 class="card-title contents_title">콘텐츠상세정보 </h2>
            </header>
            <div class="card-body contentDownForm" style="visibility:visible;background: #f2f2f2;min-height:150px;width:800px;"></div>
        </section>
    </div>
    <a class="modal-dismiss"></a>
    <script>
        $(document).ready(function () {
            $('.contents_form_btn').on('click' , function (event) {
                document.location.href = '/contents/form/P';
            });

        });
    </script>
    <script src="<?php echo(MC_ASSETS_PATH)?>script/contents.list2.js<?php echo CSS_JS_UPDATE_DATE; ?>"></script>
