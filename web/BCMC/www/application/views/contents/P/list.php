
<section class="card">
        <header class="card-header">
           <!-- <div class="card-actions">
                <a href="#" class="card-action card-action-toggle" data-card-toggle></a>
                <a href="#" class="card-action card-action-dismiss" data-card-dismiss></a>
            </div>-->
            <div calss="card"></div>
            <div class="json_text"></div>
            <h2 class="card-title">컨텐츠리스트<button type="button" class="mb-1 mt-1 mr-1 btn btn-primary contents_form_btn" style="float:right">컨텐츠등록</button></h2>
        </header>
        <div class="card-body">
            <table class="table table-responsive-md table-striped mb-0 text-center">
                <colgroup>
                    <col width="5%" />
                    <col width="*" />
                    <col width="10%" />
                    <col width="15%" />
                    <col width="10%" />
                    <col width="12%" />
                    <col width="10%" />

                    <?php if($user_auth == '9') { ?>
                    <col width="15%" />
                    <?php } ?>
                </colgroup>
                <thead>
                <tr>
                    <th>#</th>
                    <th>제목</th>
                    <th>용량</th>
                    <th>분류</th>
                    <th>패키징상태</th>
                    <th>패키징다운로드</th>
                    <th>상품등록</th>

                    <?php if ($user_auth == '9') { ?>
                    <th>배포</th>
                    <?php } ?>
                </tr>
                </thead>
                <tbody class="list_area">
                <?php if($data->total_rows > 0){ $num_start = $data->num_start; foreach($data->rows as $key=>$val){ ?>
                <tr class="<?php echo($key % 2 == 0 ? "odd" : "even")?>" next_val="<?php echo($val->init_key)?>" user_id="<?php echo($val->userid)?>" package_status="<?php echo($val->state)?>">
                    <td><?php echo(number_format($num_start)); ?></td>
                    <td style="text-align:left;" ><a href="/contents/view/p/<?php echo($val->contents_id)?>"><?php echo($val->title)?></td>
                    <td><?php echo($val->total_realsize)?></td>
                    <td><?php echo($this->menu->categoty_list[$val->cate1])?></td>
                    <td class="status">
                        <?php
                        switch ($val->state){
                            case '3' : $progress_btn = "<button id=\"shadow-success\" class=\"btn btn-success\">완료</button>"; break;
                            default : $progress_btn = "<img src=\"".COM_ASSETS_PATH."/img/icon/loading.gif\"  alt=\"loading\" />"; break;
                        }

                        echo $progress_btn;
                        ?>
                    </td>
                    <td>
                        <?php
                        if($val->ccid && $val->ccid_ver && $val->state == 3){
                           //$down_btn = "<button id=\"shadow-success\" class=\"btn btn-success packaging_download_btn\" contents_id=\"".$val->contents_id."\">다운완료</button>";
                           $down_btn = "<button id=\"shadow-success\" class=\"btn btn-success\">다운완료</button>";
                        }elseif($val->state == 3) {
                            $down_btn = "<button id=\"shadow-success\" class=\"btn btn-primary packaging_download_btn\" contents_id=\"".$val->contents_id."\">다운받기</button>";
                        }else{
                            $down_btn = "<button id=\"shadow-success\" class=\"btn btn-default\" onclick='alert(\"패키징이 완료되지 않아서 다운받을 수 없습니다.\")'>다운대기</button>";
                        }

                        echo $down_btn;
                        ?>
                    </td>
                    <td>
                        <?php
                        switch ($val->state){
                            case '3' :
                                if($val->dataid) $product_btn = "<button id=\"shadow-success\" class=\"btn btn-success\">완료</button>";
                                elseif(empty($val->dataid) && $val->ipfs_json_data) $product_btn = "<button id=\"shadow-success\" class=\"btn btn-primary  product_commit_btn\" contents_id=\"".$val->contents_id."\">상품등록</button>";
                                else $product_btn = "<button id=\"shadow-success\" class=\"btn btn-default\">미등록</button>";
                                break;
                            default : $product_btn = "<button id=\"shadow-success\" class=\"btn btn-default\">미등록</button>";
                        }

                        echo $product_btn;
                        ?>
                    </td>

                    <?php if ($user_auth == '9') { ?>
                    <td>
                        <?php
                        if ($val->stop_publish === 'Y')
                        {
                            $publish_btn = "<button id=\"shadow-success\" class=\"btn btn-danger\">배포중단완료</button>";
                        }else{
                            switch ($val->state){
                                case '3' :
                                    if($val->dataid) $publish_btn = "<button id=\"shadow-success\" class=\"btn btn-primary  stop_publish_commit_btn\" contents_id=\"".$val->contents_id."\">배포중단요청</button>";
                                    elseif(empty($val->dataid) && $val->ipfs_json_data) $publish_btn = "<button id=\"shadow-success\" class=\"btn btn-default\">배포중단</button>";
                                    else $publish_btn = "<button id=\"shadow-success\" class=\"btn btn-default\">배포가능</button>";
                                    break;
                                default : $publish_btn = "<button id=\"shadow-success\" class=\"btn btn-default\">배포가능</button>";
                            }
                        }

                        echo $publish_btn;
                        ?>
                    </td>
                    <?php } ?>

                 <!--   <td class="actions">
                        <a href=""><i class="fas fa-pencil-alt"></i></a>
                        <a href="" class="delete-row"><i class="far fa-trash-alt"></i></a>
                    </td>-->
                </tr>
                <?php $num_start--; }} ?>
                </tbody>
            </table>
           <!-- <div id="ajaxpaging"></div>-->
            <?php echo($paging)?>
            <div class="abcc"></div>
    </section>

    <script>
        // function stop_publish() {
        //     if( confirm('한번 배포가 중단되면, 더이상 사용할 수 없습니다.') ) {
        //         // true
        //     } else {
        //         // false
        //     }
        // }

        $(document).ready(function () {
            $('.contents_form_btn').on('click' , function (event) {
                document.location.href = '/contents/form/P';
            });
        });
    </script>
    <script src="<?php echo(MC_ASSETS_PATH)?>script/contents.list.js<?php echo CSS_JS_UPDATE_DATE; ?>"></script>