
</div>
<!-- end: page -->
    </section>
    </div>
</section>
<div id="mask" style="position:absolute;left:0px;top:0px;display:none;background-color:#000;z-index:999999999999999999;"></div>
<div id="loading_ajax">
    <!--<div class="lds-roller"><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div></div>-->
    <div class="img"><img src="<?php echo COM_ASSETS_PATH; ?>/img/loading.gif" alt="로딩"><span class="loading_msg">로딩중..</span></div>
</div>

</body>
</html>
<script>
$(function () {
    $.account_balance_callback();

});
</script>