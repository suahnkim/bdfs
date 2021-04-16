<link rel="stylesheet" href="<?php echo COM_ASSETS_PATH; ?>/css/bootstrap-tagsinput.css<?php echo CSS_JS_UPDATE_DATE; ?>" />
<link rel="stylesheet" href="<?php echo COM_ASSETS_PATH; ?>/css/basic.css<?php echo CSS_JS_UPDATE_DATE; ?>" />
<link rel="stylesheet" href="<?php echo COM_ASSETS_PATH; ?>/css/dropzone.css<?php echo CSS_JS_UPDATE_DATE; ?>" />
<div class="col-lg-12">
    <form class="uploadForm" novalidate="novalidate" method="POST" action="/contents/form_submit">
        <input type="hidden" name="folderpath">
        <input type="hidden" name="user_id" value="<?php echo($user->eth_account)?>">
        <input type="hidden" name="id_sign" value="">
        <input type="hidden" name="main_img" value="">
        <input type="hidden" name="sub_img" value="">
        <div class="card">
            <header class="card-header">
              <!--  <div class="card-actions">
                    <a href="#" class="card-action card-action-toggle" data-card-toggle=""></a>
                    <a href="#" class="card-action card-action-dismiss" data-card-dismiss=""></a>
                </div>
-->
                <h2 class="card-title">컨텐츠업로드</h2>

            </header>
            <div class="card-body">
                <div class="form-group row">
                    <div class="col-sm-2">

                    </div>
                    <div class="col-sm-10">

                        <div style="border-radius: 5px; background:#ccc; border:1px solid #bbb;height:150px;text-align:center;float:left;cursor:pointer;word-break: break-all;display:table-cell;vertical-align: middle;position:relative" class="col-sm-2 main-img-btn main_filename" sel_img_type="S">+ 대표이미지</div>
                        <div class="col-sm-10" style="float:left;">
                            <div class="text-left">
                                파일수 : <span class="file_cnt">0</span> | 총용량 : <span class="file_size">0</span>

                                <div style="float:right;display: table;">
                                    <button type="button" class="mb-1 mt-1 mr-1 btn btn-primary btn-upload" upload-type="F">폴더선택</button> <button type="button" class="mb-1 mt-1 mr-1 btn btn-success btn-upload" upload-type="C">파일선택</button>
                                </div>
                            </div>
                            <div style="clear:both;border:1px solid #ccc;margin-right:10px;min-height:100px;overflow: auto;" class="col-sm-12">
                                <ul class="filelist col-sm-12" style="list-style:none;"></ul>
                            </div>
                            <div class="col-sm-10" style="margin-top:5px;">
                                <div class="checkbox-custom chekbox-primary" style="float:left;">
                                    <input id="watermarking" value="Y" type="checkbox" name="watermarking" >
                                    <label for="watermarking">watermarking&nbsp;</label>
                                </div>
                                <div class="checkbox-custom chekbox-primary" style="float:left;">
                                    <input id="drm" value="y" type="checkbox" name="drm" >
                                    <label for="DRM">DRM</label>
                                </div>
                                <div class="checkbox-custom chekbox-primary" style="float:left;margin-left:3px;">
                                    <input id="is_adult" value="Y" type="checkbox" name="is_adult" >
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
                                <option value="<?php echo($key)?>"><?php echo($val)?></option>
                                <?
                            }
                            ?>
                        </select>
                    </div>
                </div>
                <div class="form-group row">
                    <label class="col-sm-2 control-label text-sm-right pt-2">제목<span class="required">*</span></label>
                    <div class="col-sm-10">
                        <input type="text" name="cont_name" class="form-control" placeholder="제목을 입력해주세요" required="">
                    </div>
                </div>
                <div class="form-group row">
                    <label class="col-sm-2 control-label text-sm-right pt-2">저작권료<span class="required">*</span></label>
                    <div class="col-sm-10">
                        <input type="text" name="fee" class="form-control" placeholder="저작권료" required="" value="100">
                    </div>
                </div>
                <div class="form-group row">
                    <label class="col-sm-2 control-label text-sm-right pt-2">내용 <span class="required">*</span></label>
                    <div class="col-sm-10">
                        <textarea name="contents" rows="5" class="form-control" placeholder="내용을 입력해 주세요" required="" >테스트</textarea>
                    </div>
                </div>
                <div class="form-group row">
                    <label class="col-sm-2 control-label text-sm-right pt-2">관심Tag</label>
                    <div class="col-sm-10">
                        <input name="hash_tags" id="tags-input" data-role="tagsinput" data-tag-class="badge badge-primary" class="form-control" value="" placeholder="예) 드라마 , 영화 , 도서 , 출판">
                    </div>
                </div>
               <div class="form-group row">
                    <label class="col-sm-2 control-label text-sm-right pt-2">이미지선택 + </label>
                    <div class="col-sm-10">
                        <button type="button" class="mb-1 mt-1 mr-1 btn btn-primary main-img-btn" sel_img_type="M">다중 이미지선택 가능</button>
                        <div class="clear:both;">
                            <ul style="list-style:none;float:left;width:100%;border:1px dotted #ccc;border-radius: 5px;margin:5px; 0;padding:5px;display:none" class="multi_img_list"></ul>
                        </div>
                    </div>
                </div>

                <div class="form-group row">
                    <label class="col-sm-2 control-label text-sm-right pt-2">국가 코드</label>
                    <div class="col-sm-10 ">
                        <select class="form-control mb-3" name="country">
                            <option value="">국가 코드 선택</option>
                            <option value="US">미국</option>
                            <option value="KR">한국</option>
                        </select>
                    </div>
                </div>
                <div class="form-group row">
                    <label class="col-sm-2 control-label text-sm-right pt-2">비디오 언어</label>
                    <div class="col-sm-10 ">
                        <select class="form-control mb-3" name="original_spoken_locale">
                            <option value="">비디오 언어 선택</option>
                            <option value="ko">한글</option>
                            <option value="en-US">영어(미국)</option>
                            <option value="en-GB">영어(영국)</option>
                        </select>
                    </div>
                </div>
                <div class="form-group row">
                    <label class="col-sm-2 control-label text-sm-right pt-2">요약본 <span class="required">*</span></label>
                    <div class="col-sm-10">
                        <textarea name="synopsis" rows="5" class="form-control" placeholder="요약본을 입력해 주세요" required=""></textarea>
                    </div>
                </div>
                <div class="form-group row">
                    <label class="col-sm-2 control-label text-sm-right pt-2">제작사</label>
                    <div class="col-sm-10">
                        <input type="text" name="production_company" class="form-control" placeholder="제작사를 입력해주세요">
                    </div>
                </div>
                <div class="form-group row">
                    <label class="col-sm-2 control-label text-sm-right pt-2">Copyright</label>
                    <div class="col-sm-10">
                        <input type="text" name="copyright_cline" class="form-control" placeholder="Copyright를 입력해주세요">
                    </div>
                </div>
                <div class="form-group row">
                    <label class="col-sm-2 control-label text-sm-right pt-2">개봉일</label>
                    <div class="col-sm-10">
                        <input type="text" name="theatrical_release_date" class="form-control" placeholder="개봉일을 입력해주세요" autocomplete="off">
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
                            <option value="12">12</option>
                            <option value="15">15</option>
                            <option value="18">18</option>
                        </select>
                    </div>
                </div>
                <div class="form-group row cast_items">
                    <div class="cast_item row col-sm-12 pl-0 pr-0">
                        <label class="col-sm-2 control-label text-sm-right pt-2">캐스팅</label>
                        <div class="col-sm-10 pr-0">
                            <div class="col-sm-12 pr-0"><input type="text" name="cast_name[]" class="form-control" placeholder="배우 이름"></div>
                            <div class="col-sm-12 pt-2 pr-0"><input type="text" name="cast_cast_name[]" class="form-control" placeholder="배역 이름"></div>
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <div class="col-sm-12 text-center">
                        <button type="button" class="btn btn-primary cast_item_add_btn">캐스팅 추가</button>
                    </div>
                </div>
                <div class="form-group row crew_items">
                    <div class="crew_item row col-sm-12 pl-0 pr-0">
                        <label class="col-sm-2 control-label text-sm-right pt-2">제작자</label>
                        <div class="col-sm-10 pr-0">
                            <div class="col-sm-12 pr-0"><input type="text" name="crew_name[]" class="form-control" placeholder="제작자 이름"></div>
                            <div class="col-sm-12 pt-2 pr-0"><input type="text" name="crew_role[]" class="form-control" placeholder="제작자 역할"></div>
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <div class="col-sm-12 text-center">
                        <button type="button" class="btn btn-primary crew_item_add_btn">제작자 추가</button>
                    </div>
                </div>
            </div>
            <footer class="card-footer">
                <div class="row justify-content-end">
                    <div class="col-sm-6 text-right">
                        <button type="submit" class="btn btn-primary btn-submit">Submit</button>
                        <button type="reset" class="btn btn-default">Reset</button>
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