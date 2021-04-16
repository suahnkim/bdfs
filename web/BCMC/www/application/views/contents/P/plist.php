<section class="card">
    <header class="card-header">
        <!-- <div class="card-actions">
             <a href="#" class="card-action card-action-toggle" data-card-toggle></a>
             <a href="#" class="card-action card-action-dismiss" data-card-dismiss></a>
         </div>-->
        <div calss="card"></div>

        <h2 class="card-title">상품정보등록된 컨텐츠리스트</h2>
    </header>
    <div class="card-body">
        <table class="table table-responsive-md table-striped mb-0 text-center">
            <thead>

            <tr>
                <th style="width:5%">#</th>
                <th style="width:*">제목</th>
                <th style="width:10%">용량</th>
                <th style="width:10%">분류</th>
            </tr>
            </thead>
            <tbody class="list_area">
            <?php if($data->total_rows > 0){ $num_start = $data->num_start; foreach($data->rows as $key=>$val){ ?>
                <tr class="<?php echo($key % 2 == 0 ? "odd" : "even")?>" next_val="<?php echo($val->init_key)?>" user_id="<?php echo($val->userid)?>" package_status="<?php echo($val->state)?>">
                    <td><?php echo(number_format($num_start)); ?></td>
                    <td style="text-align:left;" ><a href="/contents/view/p/<?php echo($val->contents_id)?>"><?php echo($val->title)?></td>
                    <td><?php echo($val->total_realsize)?></td>
                    <td><?php echo($this->menu->categoty_list[$val->cate1])?></td>
                    </td>
                </tr>
                <?php $num_start--; }} ?>
            </tbody>
        </table>
        <?php echo($paging)?>

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
<script src="<?php echo(MC_ASSETS_PATH)?>script/contents.plist.js<?php echo CSS_JS_UPDATE_DATE; ?>"></script>