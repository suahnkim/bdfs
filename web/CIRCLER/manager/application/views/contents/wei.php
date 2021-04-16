<p class="subTit">WEI 전환 현황</p>
<table class="sty02">
    <colgroup>
        <col width="15%"/><col width="35%"/><col width="15%"/><col width="35%"/>
    </colgroup>
    <tr>
        <th>전환일</th>
        <td colspan="3"><span class="alignment01 "><input type="text" name="start_date" placeholder="전체" class="inp01 jca-date-picker"/> ~ <input type="text" name="end_date" placeholder="전체" class="inp01 jca-date-picker" /></span><span class="alignment02 btn_calender_list"><button class="btn_gray01" start_date="<?php echo(date('Y-m-d' ))?>" end_date="<?php echo(date('Y-m-d' ))?>">당일</button><button class="btn_gray01" start_date="<?php echo(date('Y-m-d' , strtotime(' - 1 month' , strtotime(date('Y-m-d')))))?>" end_date="<?php echo(date('Y-m-d' ))?>">1개월</button><button class="btn_gray01" start_date="<?php echo(date('Y-m-d' , strtotime(' - 3 month' , strtotime(date('Y-m-d')))))?>" end_date="<?php echo(date('Y-m-d' ))?>">3개월</button></span></td>
    </tr>
    <tr>
        <th>구분</th>
        <td>
            <div class="select">
                <select name="" style="width:130px;">
                    <option value="" selected>전체</option>
                    <option value="">WEI → ETH</option><option value="">ETH → WEI</option>
                </select>
            </div>
        </td>
        <th>회원<br/>(이메일 주소)</th>
        <td><input type="text" name="" placeholder="이메일 주소 입력"  class="inp03" style="width:100%"/></td>
    </tr>
</table>
<p class="btnbox"><button class="btn_blue01">검색</button></p>
<table class="sty04">
    <colgroup>
        <col width="15%"/><col width="35%"/><col width="15%"/><col width="35%"/>
    </colgroup>
    <tr>
        <th>구매건수</th>
        <td>2건</td>
        <th>구매금액</th>
        <td>1,050 WEI</td>
    </tr>
</table>
<ul class="memSearch" style="margin-top:30px">
    <li>검색건수 <span class="searchNum">총 3건</span></li>
    <li><div class="select">
            <select name="" style="width:130px;">
                <option value="" selected>20개씩 보기</option>
                <option value="">50개씩 보기</option><option value="">100개씩 보기</option>
            </select>
        </div>
    </li>
</ul>
<table class="sty03">
    <colgroup>
        <col width="10%"/><col width="20%"/><col width="10%"/><col width="10%"/><col width="15%"/><col width="*"/>
    </colgroup>
    <tr>
        <th>콘텐츠 번호</th><th>콘텐츠 제목</th><th>용량</th><th>구매금액</th><th>구매일</th><th>구매 회원(이메일 주소)</th>
    </tr>
    <tr>
        <td>24</td><td>U20 월드컵 분석</td><td>1.2GB</td><td>250</td><td>2019-09-23<br/>16:02:35</td><td>honggildong@naver.com</td>
    </tr>
    <tr>
        <td>16</td><td>베트남 여행기</td><td>1.2GB</td><td>250</td><td>2019-09-23<br/>16:02:35</td><td>dev@daum.net</td>
    </tr>
    <tr>
        <td>12</td><td>한국한국</td><td>1.2GB</td><td>250</td><td>2019-09-23<br/>16:02:35</td><td>pnstory@daum.net</td>
    </tr>
</table>
<ul class="pagenum">
    <li class="arr"><i class="fas fa-chevron-left"></i></li><li class="on"><a href="#">1</a></li><li><a href="#">2</a></li><li><a href="#">3</a></li><li><a href="#">4</a></li><li><a href="#">5</a></li><li class="arr"><i class="fas fa-chevron-right"></i></li>
</ul>
<script>
    $(document).ready(function () {
        $('.btn_calender_list button').on('click' , function (event) {
            event.stopPropagation();
            event.preventDefault();

            $('input[name=start_date]').val($(this).attr('start_date'));
            $('input[name=end_date]').val($(this).attr('end_date'));
        });
        $('select[name=pageSize]').on('change' , function (event) {
            event.stopPropagation();
            event.preventDefault();

            $('form').submit();
        });
    });
</script>