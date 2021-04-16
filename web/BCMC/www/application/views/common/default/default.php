<style>
    .balance_wrap {display:block; width:100%; height:auto; margin:0; padding:0;}
    .balance_items {list-style:none; display:flex; flex-direction:column; align-items:flex-start; justify-content:flex-start; width:100%; height:auto; margin:0; padding:0;}
    .balance_item {flex:1 1 auto; display:flex; flex-direction:row; flex-wrap:nowrap; align-items:flex-start; justify-content:flex-start; margin:10px 0 0 0; padding:0;}
    .balance_item:first-child {margin-top:0;}
</style>

<div class="card-body text-center">
    <!--<img src="<?php echo COM_ASSETS_PATH; ?>/img/coming_soon.jpg">-->

    <div class="balance_wrap">
        <ul class="balance_items">
            <li class="balance_item">
                <span class="balance_item_title">ethBalance:&nbsp;</span>
                <span class="balance_item_value eth">0</span>
            </li>
            <li class="balance_item">
                <span class="balance_item_title">dappBalance:&nbsp;</span>
                <span class="balance_item_value dapp">0</span>
            </li>
        </ul>
    </div>
</div>