<section class="carr">
    <section class="card">
        <header class="card-header">
            <!-- <div class="card-actions">
                 <a href="#" class="card-action card-action-toggle" data-card-toggle></a>
                 <a href="#" class="card-action card-action-dismiss" data-card-dismiss></a>
             </div>-->
            <div calss="card"></div>

            <h2 class="card-title">승인요청리스트 &nbsp;<button type="button" class="mb-1 mt-1 mr-1 btn btn-success btn-approvals" style="float:right;">승인하기</button></h2>
        </header>
        <div class="card-body request_list">
            <table class="table table-responsive-md mb-0">
                <thead>
                <tr>
                    <th><input type="checkbox" name="all_chk"></th>
                    <th>no</th>
                    <th>accountId</th>
                    <th>account_type</th>
                </tr>
                </thead>
                <tbody>
                <tr>
                    <td colspan="4" style="text-align:center;" class="loading"><img src="<?php echo COM_ASSETS_PATH; ?>/img/icon/loading.gif"  alt="loading" /> 데이터를 불러오는중....</td>
                </tr>
                <!--<tr>
                    <td>1</td>
                    <td>Mark</td>
                    <td>Otto</td>
                    <td>@mdo</td>
                    <td class="actions">
                        <a href=""><i class="fas fa-pencil-alt"></i></a>
                        <a href="" class="delete-row"><i class="far fa-trash-alt"></i></a>
                    </td>
                </tr>
                -->
                </tbody>
            </table>
        </div>
    </section>
    <script src="<?php echo(MC_ASSETS_PATH)?>script/auth.request.list.js<?php echo CSS_JS_UPDATE_DATE; ?>"></script>