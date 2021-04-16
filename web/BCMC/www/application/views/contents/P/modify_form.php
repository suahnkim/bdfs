<link rel="stylesheet" href="<?php echo COM_ASSETS_PATH; ?>/css/bootstrap-tagsinput.css<?php echo CSS_JS_UPDATE_DATE; ?>" />
<link rel="stylesheet" href="<?php echo COM_ASSETS_PATH; ?>/css/basic.css<?php echo CSS_JS_UPDATE_DATE; ?>" />
<link rel="stylesheet" href="<?php echo COM_ASSETS_PATH; ?>/css/dropzone.css<?php echo CSS_JS_UPDATE_DATE; ?>" />
<div class="col-lg-12">
    <form class="modifyUploadForm" novalidate="novalidate" method="POST" action="/contents/form_submit">
        <input type="hidden" name="folderpath" value="<?php echo $data->folder_name; ?>">
        <input type="hidden" name="user_id" value="<?php echo($user->eth_account)?>">
        <input type="hidden" name="id_sign" value="">
        <input type="hidden" name="main_img" value="<?php echo $data->main_img; ?>">
        <input type="hidden" name="sub_img" value="<?php echo $data->sub_img; ?>">

        <input type="hidden" name="contents_id" value="<?php echo $data->contents_id; ?>">


        <div class="card">
            <header class="card-header">
                <h2 class="card-title">컨텐츠수정</h2>
            </header>

            <div class="card-body">
                <div class="form-group row">
                    <div class="col-sm-2">

                    </div>
                    <div class="col-sm-10">

                        <div style="border-radius: 5px; background:#ccc; border:1px solid #bbb;height:150px;text-align:center;float:left;cursor:pointer;word-break: break-all;display:table-cell;vertical-align: middle;position:relative; cursor:default;" class="col-sm-2 main-img-btn main_filename" sel_img_type="S" data-exec="N">
                            <?php if( $data->main_img_src ) { ?>
                            <img src="<?php echo $data->main_img_src; ?>" style="width:100%;height:100%;overflow:hidden;display:flex;;justify-content:center;align-items:center;">
                            <?php } ?>
                        </div>
                        <div class="col-sm-10" style="float:left;">
                            <div class="text-left">
                                파일수 : <span class="file_cnt"><?php echo count($data->rows); ?></span> | 총용량 : <span class="file_size"><?php echo($data->size_text); ?></span>
                            </div>
                            <div style="clear:both;border:1px solid #ccc;margin-right:10px;min-height:100px;overflow: auto;" class="col-sm-12">
                                <ul class="filelist col-sm-12" style="list-style:none;">

                                    <?php if( count($data->rows) ) { ?>
                                        <?php foreach($data->rows as $row) { ?>
                                            <?php $fileInfo = $row->folder . '\\' . $row->filename . '|&|' . $row->size; ?>

                                    <li class="target filelist_item col-sm-12">
                                        <div class="col-sm-10" style="width:80%;float:left;text-align:left">
                                            <input type="hidden" name="fileinfo[]" value='<?php echo $fileInfo; ?>'><?php echo $row->filename; ?>
                                        </div>
                                        <div class="col-sm-2" style="width:20%;float:left;text-align:right;"><?php echo $row->realsize_str; ?></div>
                                    </li>

                                        <?php } ?>

                                    <?php } ?>

                                </ul>
                            </div>
                            <div class="col-sm-10" style="margin-top:5px;">
                                <div class="checkbox-custom chekbox-primary" style="float:left;">
                                    <input id="watermarking" value="Y" type="checkbox" name="watermarking" <?php if($data->watermarking === 'Y') { echo 'checked="checked"'; } ?> disabled>
                                    <label for="watermarking">watermarking&nbsp;</label>
                                </div>
                                <div class="checkbox-custom chekbox-primary" style="float:left;">
                                    <input id="drm" value="Y" type="checkbox" name="drm" <?php if($data->drm === 'Y') { echo 'checked="checked"'; } ?> disabled>
                                    <label for="DRM">DRM</label>
                                </div>
                                <div class="checkbox-custom chekbox-primary" style="float:left;margin-left:3px;">
                                    <input id="is_adult" value="Y" type="checkbox" name="is_adult" <?php if($data->is_adult === 'Y') { echo 'checked="checked"'; } ?> disabled>
                                    <label for="is_adult" style="color:red;"> +19 </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="form-group row">
                    <label class="col-sm-2 control-label text-sm-right pt-2">분류<span class="required">*</span></label>
                    <div class="col-sm-10 ">
                        <select class="form-control mb-3" name="genre">
                            <?php
                            foreach($this->menu->categoty_list as $key=>$val){
                                ?>
                                <option value="<?php echo($key)?>" <?php if($data->cate1 === $key) { echo 'selected="selected"'; } ?>><?php echo($val)?></option>
                                <?
                            }
                            ?>
                        </select>
                    </div>
                </div>
                <div class="form-group row">
                    <label class="col-sm-2 control-label text-sm-right pt-2">제목<span class="required">*</span></label>
                    <div class="col-sm-10">
                        <input type="text" name="cont_name" class="form-control" value="<?php echo($data->title); ?>" placeholder="제목을 입력해주세요" required="">
                    </div>

                </div>
                <div class="form-group row">
                    <label class="col-sm-2 control-label text-sm-right pt-2">저작권료</label>
                    <div class="col-sm-10">
                        <input type="text" name="fee" class="form-control" placeholder="저작권료" required="" value="<?php echo $data->cash; ?>" disabled>
                    </div>
                </div>
                <div class="form-group row">
                    <label class="col-sm-2 control-label text-sm-right pt-2">내용 <span class="required">*</span></label>
                    <div class="col-sm-10">
                        <textarea name="contents" rows="5" class="form-control" placeholder="내용을 입력해 주세요" required=""><?php echo $data->contents; ?></textarea>
                    </div>
                </div>
                <div class="form-group row">
                    <label class="col-sm-2 control-label text-sm-right pt-2">관심Tag</label>
                    <div class="col-sm-10">
                        <input name="hash_tags" id="tags-input" data-role="tagsinput" data-tag-class="badge badge-primary" class="form-control" value="<?php echo $data->hash_tags; ?>" placeholder="예) 드라마 , 영화 , 도서 , 출판">
                    </div>
                </div>
                <div class="form-group row">
                    <label class="col-sm-2 control-label text-sm-right pt-2">이미지선택 + </label>
                    <div class="col-sm-10">
                        <div class="clear:both;">
                            <ul style="list-style: none; float: left; width: 100%; border: 1px dotted rgb(204, 204, 204); border-radius: 5px; margin: 5px; padding: 5px;" class="multi_img_list">

                                <?php if( count($data->sub_img_arr) ) { ?>
                                    <?php foreach($data->sub_img_arr as $sub_img) { ?>

                                <li style="width:120px;height:100px;float:left;margin:10px;border-radius: 5px;background:#ddd;text-align:center;position:relative;overflow:hidden;display:flex;;justify-content:center;align-items:center;"
                                    tmp-filepath="<?php echo $sub_img[0]; ?>"
                                    tmp-filesize="<?php echo $sub_img[1]; ?>"
                                    tmp-width="<?php echo $sub_img[2]; ?>"
                                    tmp-height="<?php echo $sub_img[3]; ?>"
                                    tmp-filename="<?php echo $sub_img[4]; ?>"
                                >
                                    <span style="width:120px;"><img src="<?php echo $sub_img[4]; ?>" style="width:100px;height:auto;"></span>
                                </li>

                                    <?php } ?>
                                <?php } ?>

                            </ul>
                        </div>
                    </div>
                </div>

                <div class="form-group row">
                    <label class="col-sm-2 control-label text-sm-right pt-2">국가 코드</label>
                    <div class="col-sm-10 ">
                        <select class="form-control mb-3" name="country">
                            <option value="">국가 코드 선택</option>
                            <option value="US" <?php if($data->metainfo->metadata->country === 'US') { echo 'selected="selected"'; } ?>>미국</option>
                            <option value="KR" <?php if($data->metainfo->metadata->country === 'KR') { echo 'selected="selected"'; } ?>>한국</option>
                        </select>
                    </div>
                </div>
                <div class="form-group row">
                    <label class="col-sm-2 control-label text-sm-right pt-2">비디오 언어</label>
                    <div class="col-sm-10 ">
                        <select class="form-control mb-3" name="original_spoken_locale">
                            <option value="">비디오 언어 선택</option>
                            <option value="ko" <?php if($data->metainfo->metadata->original_spoken_locale === 'ko') { echo 'selected="selected"'; } ?>>한글</option>
                            <option value="en-US" <?php if($data->metainfo->metadata->original_spoken_locale === 'en-US') { echo 'selected="selected"'; } ?>>영어(미국)</option>
                            <option value="en-GB" <?php if($data->metainfo->metadata->original_spoken_locale === 'en-GB') { echo 'selected="selected"'; } ?>>영어(영국)</option>
                        </select>
                    </div>
                </div>
                <div class="form-group row">
                    <label class="col-sm-2 control-label text-sm-right pt-2">요약본 <span class="required">*</span></label>
                    <div class="col-sm-10">
                        <textarea name="synopsis" rows="5" class="form-control" placeholder="요약본을 입력해 주세요" required=""><?php echo isset($data->metainfo->metadata->synopsis) ? $data->metainfo->metadata->synopsis : ''; ?></textarea>
                    </div>
                </div>
                <div class="form-group row">
                    <label class="col-sm-2 control-label text-sm-right pt-2">제작사</label>
                    <div class="col-sm-10">
                        <input type="text" name="production_company" class="form-control" value="<?php echo isset($data->metainfo->metadata->production_company) ? $data->metainfo->metadata->production_company : ''; ?>" placeholder="제작사를 입력해주세요">
                    </div>
                </div>
                <div class="form-group row">
                    <label class="col-sm-2 control-label text-sm-right pt-2">Copyright</label>
                    <div class="col-sm-10">
                        <input type="text" name="copyright_cline" class="form-control" value="<?php echo isset($data->metainfo->metadata->copyright_cline) ? $data->metainfo->metadata->copyright_cline : ''; ?>" placeholder="Copyright를 입력해주세요">
                    </div>
                </div>
                <div class="form-group row">
                    <label class="col-sm-2 control-label text-sm-right pt-2">개봉일</label>
                    <div class="col-sm-10">
                        <input type="text" name="theatrical_release_date" class="form-control" value="<?php echo isset($data->metainfo->metadata->theatrical_release_date) ? $data->metainfo->metadata->theatrical_release_date : ''; ?>" placeholder="개봉일을 입력해주세요" autocomplete="off">
                    </div>
                </div>
                <div class="form-group row">
                    <label class="col-sm-2 control-label text-sm-right pt-2">장르</label>
                    <div class="col-sm-10 ">
                        <select class="form-control" name="basicMeta_genre">
                            <?php
                            foreach($this->menu->categoty_list as $key=>$val){
                                ?>
                                <option value="<?php echo($key)?>"><?php echo($val)?></option>
                                <option value="<?php echo($key)?>" <?php if(isset($data->metainfo->metadata->genre) && $data->metainfo->metadata->genre === $key) { echo 'selected="selected"'; } ?>><?php echo($val)?></option>
                                <?
                            }
                            ?>
                        </select>
                    </div>
                </div>
                <div class="form-group row">
                    <label class="col-sm-2 control-label text-sm-right pt-2">시청등급</label>
                    <div class="col-sm-10 ">
                        <select class="form-control mb-3" name="ratings">
                            <option value="">시청등급 선택</option>
                            <option value="12" <?php if(isset($data->metainfo->metadata->ratings) && $data->metainfo->metadata->ratings === '12') { echo 'selected="selected"'; } ?>>12</option>
                            <option value="15" <?php if(isset($data->metainfo->metadata->ratings) && $data->metainfo->metadata->ratings === '15') { echo 'selected="selected"'; } ?>>15</option>
                            <option value="18" <?php if(isset($data->metainfo->metadata->ratings) && $data->metainfo->metadata->ratings === '18') { echo 'selected="selected"'; } ?>>18</option>
                        </select>
                    </div>
                </div>
                <div class="form-group row cast_items">
                    <?php if( isset($data->metainfo->metadata->cast) && count($data->metainfo->metadata->cast) ) { ?>
                        <?php $firstCast = 'Y'; ?>
                        <?php foreach($data->metainfo->metadata->cast as $cast) { ?>

                    <div class="cast_item row col-sm-12 pl-0 pr-0 pb-2">
                        <label class="col-sm-2 control-label text-sm-right pt-2"><?php echo $firstCast === 'Y' ? '캐스팅' : '&nbsp;'; ?></label>
                        <div class="col-sm-10 pr-0">
                            <div class="col-sm-12 pr-0"><input type="text" name="cast_name[]" class="form-control" value="<?php echo $cast->name ? $cast->name : ''; ?>" placeholder="배우 이름"></div>
                            <div class="col-sm-12 pt-2 pr-0"><input type="text" name="cast_cast_name[]" class="form-control" value="<?php echo $cast->cast_name ? $cast->cast_name : ''; ?>" placeholder="배역 이름"></div>
                        </div>
                    </div>

                            <?php $firstCast = 'N'; ?>
                        <?php } // foreach End ?>
                    <?php } else { ?>

                    <div class="cast_item row col-sm-12 pl-0 pr-0">
                        <label class="col-sm-2 control-label text-sm-right pt-2">캐스팅</label>
                        <div class="col-sm-10 pr-0">
                            <div class="col-sm-12 pr-0"><input type="text" name="cast_name[]" class="form-control" placeholder="배우 이름"></div>
                            <div class="col-sm-12 pt-2 pr-0"><input type="text" name="cast_cast_name[]" class="form-control" placeholder="배역 이름"></div>
                        </div>
                    </div>

                    <?php } // end if ?>
                </div>
                <div class="form-group">
                    <div class="col-sm-12 text-center">
                        <button type="button" class="btn btn-primary cast_item_add_btn">캐스팅 추가</button>
                    </div>
                </div>
                <div class="form-group row crew_items">
                    <?php if( isset($data->metainfo->metadata->crew) && count($data->metainfo->metadata->crew) ) { ?>
                        <?php $firstCrew = 'Y'; ?>
                        <?php foreach($data->metainfo->metadata->crew as $crew) { ?>

                    <div class="crew_item row col-sm-12 pl-0 pr-0 pb-2">
                        <label class="col-sm-2 control-label text-sm-right pt-2"><?php echo $firstCrew === 'Y' ? '제작자' : '&nbsp;'; ?></label>
                        <div class="col-sm-10 pr-0">
                            <div class="col-sm-12 pr-0"><input type="text" name="crew_name[]" class="form-control" value="<?php echo $crew->name ? $crew->name : ''; ?>" placeholder="제작자 이름"></div>
                            <div class="col-sm-12 pt-2 pr-0"><input type="text" name="crew_role[]" class="form-control" value="<?php echo $crew->role ? $crew->role : ''; ?>" placeholder="제작자 역할"></div>
                        </div>
                    </div>

                            <?php $firstCrew = 'N'; ?>
                        <?php } // foreach end ?>
                    <?php } else { ?>

                    <div class="crew_item row col-sm-12 pl-0 pr-0">
                        <label class="col-sm-2 control-label text-sm-right pt-2">제작자</label>
                        <div class="col-sm-10 pr-0">
                            <div class="col-sm-12 pr-0"><input type="text" name="crew_name[]" class="form-control" placeholder="제작자 이름"></div>
                            <div class="col-sm-12 pt-2 pr-0"><input type="text" name="crew_role[]" class="form-control" placeholder="제작자 역할"></div>
                        </div>
                    </div>

                    <?php } // if end ?>
                </div>
                <div class="form-group">
                    <div class="col-sm-12 text-center">
                        <button type="button" class="btn btn-primary crew_item_add_btn">제작자 추가</button>
                    </div>
                </div>
            </div>
            <footer class="card-footer">
                <div class="row justify-content-end">
                    <div class="col-12 text-right">
                        <button type="submit" class="btn btn-primary btn-submit">Update</button>
                        <button type="button" class="btn btn-default" onclick="history.back(-1);">Cancle</button>
                    </div>
                </div>
            </footer>
    </form>
</div>
<script type="text/javascript" src="<?php echo(COM_ASSETS_PATH)?>/script/bootstrap-tagsinput.js<?php echo CSS_JS_UPDATE_DATE; ?>"></script>
<script src="<?php echo(MC_ASSETS_PATH)?>script/contents.form.js<?php echo CSS_JS_UPDATE_DATE; ?>"></script>
<script src="<?php echo(COM_ASSETS_PATH)?>/script/bootstrap-datepicker.min.js<?php echo CSS_JS_UPDATE_DATE; ?>"></script>

<script>
    $(document).ready(function() {
        $('input[name="theatrical_release_date"]').datepicker({
            format: 'yyyy-mm-dd',
            autoclose: true
        });
    });
</script>