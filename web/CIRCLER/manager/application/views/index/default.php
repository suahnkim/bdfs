<ul class="coininfo">
    <li>총보유 ETH  <img src="<?php echo COM_ASSETS_PATH; ?>/img/icon_coin_eth.png" alt="">153.22 ETH</li>
    <li>총보유 WEI  <img src="<?php echo COM_ASSETS_PATH; ?>/img/icon_coin_wei.png" alt="">7,150,163 WEI </li>
</ul>
<div class="conbox">
    <span class="tit">회원 현황</span>
    <ul class="graphbox">
        <li class="graph01">
            <div class="graph_tit">(일별) 최근 15일간 가입 수 (<?php echo(date('Y'))?>년)</div>
            <div class="graphcon" id="day15">
                그래프 들어가는 자리
            </div>
        </li>
        <li class="graph01">
            <div class="graph_tit">(월별) 최근 6개월간 가입 수(<?php echo(date('Y'))?>년)</div>
            <div class="graphcon" id="month6">
                그래프 들어가는 자리
            </div>
        </li>
        <li class="graph02">
            <div class="graph_tit">최근 7일간 탈퇴 수(<?php echo(date('Y'))?>년)</div>
            <table class="sty01">
                <colgroup>
                    <col width="70%"/><col width="30%"/>
                </colgroup>
                <tr>
                    <th style="text-align:center;">날짜</th>
                    <th>탈퇴 수</th>
                </tr>
                <?php if(count($secede_data) > 0){ foreach($secede_data as $key=>$val){ ?>
                <tr>
                    <td class="c"><?php echo($val['date'])?></td>
                    <td class="c"><?php echo(number_format($val['cnt']))?></td>
                </tr>
                <?php }} ?>
            </table>
        </li>
    </ul>
</div>
<div class="conbox">
    <span class="tit">판매 현황</span>
    <ul class="graphbox">
        <li class="graph03">
            <div class="graph_tit">(일별) 최근 15일간 판매금액(<?php echo(date('Y'))?>년)</div>
            <div class="graphcon" id="product_day15">
                그래프 들어가는 자리
            </div>
        </li>
        <li class="graph04">
            <div class="graph_tit">어제(<?php echo(date('Y-m-d' , strtotime(' - 1 day' , strtotime(date('Y-m-d')))))?>) 판매금액 TOP 6 콘텐츠</div>
            <table class="sty01" style="text-align:Center;">
                <colgroup>
                    <col width="10%"/>
                    <col width="40%"/>
                    <col width="15%"/>
                    <col width="10%"/>
                    <col width="10%"/>
                    <col width="15%"/>
                </colgroup>
                <tr>
                    <th>순위</th>
                    <th>콘텐츠제목</th>
                    <th>용량</th>
                    <th style="text-align:center;">판매단가</th>
                    <th style="text-align:center;">판매건수</th>
                    <th>판매금액</th>
                </tr>
                <?php if(count($yesterday_point_data->rows) > 0){ foreach($yesterday_point_data->rows as $key=>$val){ ?>
                    <tr>
                        <td class="c"><?php echo($key + 1)?></td>
                        <td class="c"><?php echo($val->title)?></td>
                        <td class="c"><?php echo(getFileSizeStr($val->size))?></td>
                        <td class="c"><?php echo(number_format($val->real_cash))?></td>
                        <td class="c"><?php echo(number_format($val->cnt))?></td>
                        <td class="c"><?php echo(number_format($val->sum_point))?></td>
                    </tr>
                <?php }} ?>
            </table>
        </li>
    </ul>
</div>
<script>
    $(document).ready(function () {
        var chart = new CanvasJS.Chart("day15", {
            animationEnabled: true,
            title:{
                text: ""
            },
            axisY: {
                title: "",
                includeZero: false,
                suffix: " "
            },
           /* legend:{
                cursor: "pointer",
                fontSize: 16,
                itemclick: toggleDataSeries
            },*/
            toolTip:{
                shared: true
            },

            data: [<?php echo($graph1);?>]

        });
        chart.render();

        var chart2 = new CanvasJS.Chart("month6", {
            animationEnabled: true,
            title:{
                text: ""
            },
            axisY: {
                title: "",
                includeZero: false,
                suffix: " "
            },
           /* legend:{
                cursor: "pointer",
                fontSize: 16,
                itemclick: toggleDataSeries
            },*/
            toolTip:{
                shared: true
            },
            data: [<?php echo($graph2);?>]

        });
        chart2.render();

        var chart3 = new CanvasJS.Chart("product_day15", {
            animationEnabled: true,
            title:{
                text: ""
            },
            axisY: {
                title: "",
                includeZero: false,
                suffix: " "
            },
            /* legend:{
                 cursor: "pointer",
                 fontSize: 16,
                 itemclick: toggleDataSeries
             },*/
            toolTip:{
                enabled  : true,
                shared: true
            },


            data: [<?php echo($graph3);?>]

        });
        chart3.render();
    });
</script>
<script language="javascript" src="<?php echo COM_ASSETS_PATH; ?>/script/jquery.canvasjs.min.js<?php echo CSS_JS_UPDATE_DATE; ?>"></script>