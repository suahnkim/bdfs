<section class="card">
    <header class="card-header">
        <!-- <div class="card-actions">
             <a href="#" class="card-action card-action-toggle" data-card-toggle></a>
             <a href="#" class="card-action card-action-dismiss" data-card-dismiss></a>
         </div>-->
        <div calss="card"></div>

        <h2 class="card-title">회원리스트</h2>
    </header>
    <div class="card-body">
        <table class="table table-responsive-md table-striped mb-0 text-center">
            <thead>

            <tr>
                <th style="width:5%">#</th>
                <th style="width:30%">이더리움계정</th>
                <th style="width:30%">가입형태</th>
                <th style="width:30%">가입일</th>
            </tr>
            </thead>
            <tbody>
            <?php if($data->total_rows > 0){ $num_start = $data->num_start; foreach($data->rows as $key=>$val){ ?>
                <tr class="<?php echo($key % 2 == 0 ? "odd" : "even")?>" user_id="<?php echo($val->account)?>">
                    <td><?php echo(number_format($num_start)); ?></td>
                    <td style="text-align:left;" ><a href="/auth/user_view/<?php echo($val->user_info_id)?>"><?php echo($val->account)?></td>
                    <td><?php echo($val->total_realsize)?></td>
                    <td><?php echo($val->create_datetime)?></td>
                </tr>
                <?php $num_start--; }}else{ ?>
                <tr><td style="text-align:Center;line-height:30px;" colspan="4">데이터가 없습니다.</td></tr>
                <?php }?>
            </tbody>
        </table>
        <?php echo($paging)?>
</section>
