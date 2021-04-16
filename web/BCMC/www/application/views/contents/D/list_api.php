<?php
preg_match("/(([a-z0-9\-]+\.)*)([a-z0-9\-]+)\.([a-z]{3,4}|[a-z]{2,3}\.[a-z]{2})(\:[0-9]+)?$/", $_SERVER['HTTP_HOST'], $matches);
$sub_domain = null;
if($matches[1]) {
    $sub_domain = substr($matches[1], 0, -1) ? substr($matches[1], 0, -1) : 'www';
}
?>
<section class="card">
    <header class="card-header">
        <!-- <div class="card-actions">
             <a href="#" class="card-action card-action-toggle" data-card-toggle></a>
             <a href="#" class="card-action card-action-dismiss" data-card-dismiss></a>
         </div>-->
        <div calss="card"></div>

        <h2 class="card-title">전체콘텐츠</h2>
    </header>
    <div class="card-body">
        <form method="get">
            <div class="row">
                <div class="col-lg-6 col-md-12 col-sm-12">
                    <select class="form-control mb-3 col-lg-3 col-md-6 col-sm-12" name="pageSize">
                        <option value="20" <?php echo($request_params->rowPerPage == 20)?"selected":""?>>20</option>
                        <option value="50" <?php echo($request_params->rowPerPage == 50)?"selected":""?>>50</option>
                        <option value="100" <?php echo($request_params->rowPerPage == 100)?"selected":""?>>100</option>
                    </select>
                </div>
                <div class="col-lg-6 col-md-12 col-sm-12">
                    <div class="row">
                        <div class="col-lg-4 col-md-12 col-sm-12">
                                <?php $search_option_arr = array('sk_title'=>'제목' , 'sk_synops'=>'내용','keyword'=>'제목 + 내용');?>
                                <select name="search_key" class="form-control col-sm-12" >
                                    <option value="">선택하세요</option>
                                    <?php foreach($search_option_arr as $key=>$val){ ?>
                                        <option value="<?php echo($key)?>" <?php echo($key == @$request_params->search_key ? "selected" : "")?>><?php echo($val)?></option>
                                    <?php } ?>
                                </select>

                        </div>
                        <div class="col-lg-8 col-md-12 col-sm-12">
                            <div class="input-group mb-3">
                                <input type="text" name="search_value" class="form-control" value="<?php echo($request_params->search_value)?>" placeholder="검색어를 입력해주세요.">
                                <span class="input-group-append">
                                    <button type="submit" class="btn btn-success" type="button"><i class="fas fa-search"></i> 검색</button>
                                 </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
        <table class="table table-responsive-md table-striped mb-0 text-center">
            <thead>

            <tr>
                <th style="width:5%">#</th>
                <th style="width:30%">제목</th>
                <th style="width:10%">용량</th>
                <th style="width:15%">분류</th>
                <th style="width:7%">저작권료</th>
                <th style="width:8%">저작권설정</th>
                <th style="width:15%">등록일</th>
                <?php if(empty($request_params->list_type)){ ?>
                    <th style="width:10%">상태</th>
                <?php }else{ ?>
                    <th style="width:10%">유통가격</th>
                <?php } ?>
            </tr>
            </thead>
            <tbody>
            <?php if($data->total_rows > 0){ $num_start = $data->num_start; $_drm = array(); foreach($data->rows as $key=>$val){

                $_drm = array();
                if($val->drm == 'Y') $_drm[] = 'drm';
                if($val->watermarking == 'Y') $_drm[] = 'watermarking';
                if($val->is_adult == 'Y') $_drm[] = 'adult';

                ?>
                <tr class="<?php echo($key % 2 == 0 ? "odd" : "even")?>" ccid="<?php echo($val->ccid)?>" ccid_ver="<?php echo($val->ccid_ver)?>" productId="<?php echo($val->productId)?>" >
                    <td><?php echo(number_format($num_start)); ?></td>
                    <td style="text-align:left;" class="contents_modal_view"><a href="#modalAnim" class="mb-1 mt-1 mr-1 modal-with-zoom-anim ws-normal model-btn" media-contnets-id="<?php echo(@$val->contents_id)?>"><?php echo($val->title)?></a></td>
                    <td><?php echo($val->str_size)?></td>
                    <td><?php echo($this->menu->categoty_list[$val->cate1])?></td>
                    <td><?php echo(Number_Format($val->cash))?></td>
                    <td><?php echo(implode('<br>', $_drm))?></td>
                    <td><?php echo($val->regdate)?></td>
                    <td class="status contents_modal_view">
                        <?php if(empty($request_params->list_type)){ ?>
                            <?php if(@$val->productId) { ?>

                            <button id="shadow-success" class="btn btn-success">유통함</button>

                            <?php } else { ?>

                            <a href="#modalAnim" class="btn btn-danger modal-with-zoom-anim ws-normal model-btn" media-contnets-id="<?php echo(@$val->contents_id)?>">유통안함</a>

                            <?php } ?>
                        <?php }else{ ?>
                    <?php echo(number_format($val->real_cash))?>
                        <?php }?>
                    </td>
                </tr>
                <?php $num_start--; }}else{ ?>
                <tr>
                    <td colspan="8" class="text-center">검색된 컨텐츠가 없습니다.</td>
                </tr>
            <?php } ?>
            </tbody>
        </table>
        <?php echo($paging)?>
</section>
<!-- Modal Form -->
<div id="modalAnim" class="zoom-anim-dialog modal-block modal-block-primary mfp-hide">

    <section class="card" style="background:#ccc;">

        <div class="loading-overlay-showing data-loading" style="background-color: rgb(253, 253, 253); border-radius: 0px 0px 5px 5px;"><div class="bounce-loader"><div class="bounce1"></div><div class="bounce2"></div><div class="bounce3"></div></div></div>
        <header class="card-header">
            <h2 class="card-title">유통설정 </h2>
        </header>
        <div class="card-body contentSellFom" style="visibility:visible;background: #f2f2f2;min-height:150px;"></div>
    </section>

</div>
<a class="modal-dismiss"></a>
<script src="<?php echo(MC_ASSETS_PATH)?>script/contents.list_api_D.js<?php echo CSS_JS_UPDATE_DATE; ?>"></script>


<div class="lists"></div>
