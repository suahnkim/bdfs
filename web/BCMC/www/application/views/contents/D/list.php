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
                <tr class="<?php echo($key % 2 == 0 ? "odd" : "even")?>" ccid="<?php echo($val->ccid)?>" ccid_ver="<?php echo($val->ccid_ver)?>" productId="<?php echo(@$val->productId)?>">
                    <td><?php echo(number_format($num_start)); ?></td>
                    <td style="text-align:left;" class="contents_modal_view" ><a href="#modalAnim" media-contnets-id="<?php echo($val->contents_id)?>" class="mb-1 mt-1 mr-1 modal-with-zoom-anim ws-normal model-btn"><?php echo($val->title)?></a></td>
                    <td><?php echo($val->total_realsize)?></td>
                    <td><?php echo($this->menu->categoty_list[$val->cate1])?></td>
                    <td><?php echo(Number_Format($val->cash))?></td>
                    <td><?php echo(implode('<br>', $_drm))?></td>
                    <td><?php echo(date('Y-m-d H:i:s' , $val->wdate))?></td>
                    <td class="status">
                        <?php if(empty($request_params->list_type)){ ?>
                        <button id="shadow-success" class="btn btn-<?php echo(@$val->user_sell_contents_id ? 'success' : 'danger')?>"><?php echo(@$val->user_sell_contents_id ? '유통함' : '유통안함')?></button>
                        <?php }else{ ?>
                         <?php echo(number_format($val->real_cash))?>
                        <?php }?>
                    </td>
                </tr>
                <?php $num_start--; }} ?>
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
<script src="<?php echo(MC_ASSETS_PATH)?>script/contents.listD.js<?php echo CSS_JS_UPDATE_DATE; ?>"></script>

