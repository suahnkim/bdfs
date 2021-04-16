<section class="card">
    <header class="card-header">
        <!-- <div class="card-actions">
             <a href="#" class="card-action card-action-toggle" data-card-toggle></a>
             <a href="#" class="card-action card-action-dismiss" data-card-dismiss></a>
         </div>-->
        <div calss="card"></div>

        <h2 class="card-title"><?php echo($data->title)?> &nbsp;<!--<button type="button" class="mb-1 mt-1 mr-1 btn btn-primary contents_form_btn" style="float:right">컨텐츠등록</button>--></h2>
    </header>

    <div class="card-body">
        <form class="down-form">
            <input type="hidden" name="next_val" value="<?php echo($data->init_key)?>">
            <input type="hidden" name="contents_no" value="<?php echo($data->contents_id)?>">
            <input type="hidden" name="user_id" value="<?php echo($data->userid)?>">
            <input type="hidden" name="file_type" value="<?php echo($data->folder_name)?>">
            <input type="hidden" name="status" value="">

            <div class="toggle toggle-primary" data-plugin-toggle="">
                <section class="toggle active">
                    <label><i class="fas fa-plus" style="display:none;"></i><i class="fas fa-minus" style="display:none;"></i><i class="fas fa-folder-open"></i><?php echo(!$data->folder_name ? $data->rows[0]->filename : '')?></label>
                    <?php if(count($data->rows) > 0){?>
                        <div class="toggle-content" style="display: ;">
                            <ul class="file_list" style="list-style:none;">
                            <?php foreach($data->rows as $key=>$val){?>
                                <li file_no="<?php echo($val->sort)?>" contents_file_no="<?php echo($val->contents_file_id)?>" file_name="<?php echo($val->filename)?>" del_yn="N" file_size="<?php echo($val->realsize)?>" file_path="<?php echo($val->folder)?>/<?php echo($val->filename)?>">
                                <div style="float:left;width:80%;padding-left:50px;border-bottom:1px solid #ddd;height:40px;line-height:40px;"> - <?php echo($val->filename)?></div>
                                <div style="float:left;width:20%;border-bottom:1px solid #ddd;height:40px;line-height:40px;text-align:right;padding-right:50px;"><?php echo(getFileSizeStr($val->realsize))?></div>
                                </li>
                            <?php }?>
                            </ul>
                        </div>
                    <?php }?>
                </section>
            </div>
        </form>
    </div>
    <div clss="card-body text-center">
        <div  style="margin-top:50px;">
            <div class="row text-center" style="position:relative; display: -webkit-flex;display: flex;-webkit-justify-content: center;justify-content: center;-webkit-align-items: center;
  align-items: center; ">
                <a href="/contents/modify_form/p/<?php echo($data->contents_id)?>" class="mb-1 mt-1 mr-3 btn btn-primary btn-modify">수정</a>
                <button type="button" class="mb-1 mt-1 mr-1 btn btn-success btn-download" style="float:right" >다운로드</button>
            </div>
        </div>
    </div>
    <div class="card-body" style="margin-top:20px;">
        <div><?php echo($data->contents)?></div>
        <?php
        if($data->main_img && $data->ccid_ver){

            $exp_main_img =explode('|' , $data->main_img);

            //$exp_main_img = explode('\\' , $main_img);
            //$main_filename = 'http://localhost:8080/ipfs/'.$data->ccid_ver ."/basicMeta/".end($exp_main_img);
            $main_filename =  end($exp_main_img);
            //$ext = substr(strrchr(end($exp_main_img), '.'), 1);
            ?>
            <div style="margin:10px; 0;"><img src="<?php echo($main_filename)?>" style="max-width:700px;"></div>
        <?php } ?>
        <?php
        if($data->sub_img && $data->ccid_ver){

            $sub_img_exp = explode(','  , $data->sub_img);

            foreach($sub_img_exp as $key=>$val){

                $exp_sub_img = explode('|' , $val);
                //$exp_sub_img = explode('\\' , $sub_img);
                $sub_filename = end($exp_sub_img);
                $ext = substr(strrchr(end($exp_sub_img), '.'), 1);
                ?>
                <div style="margin:10px; 0;"><img src="<?php echo($sub_filename)?>" style="max-width:700px;"></div>
            <?php }} ?>

    </div>
</section>
<script src="<?php echo(MC_ASSETS_PATH)?>script/contents.view.js<?php echo CSS_JS_UPDATE_DATE; ?>"></script>