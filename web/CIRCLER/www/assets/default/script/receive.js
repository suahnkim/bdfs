$(document).ready(function () {
    $('.contentsReceiveList tr.item a').on('click' , function (event) {
        event.stopPropagation();
        event.preventDefault();
        var contents_id = $(this).parents('tr').attr('contents_id')
        document.location.href = "#contents_id="+contents_id + '&t='+new Date().getTime();;
    });

    $('.contentsReceiveList .all_chk').on('click' , function (event) {
        //event.stopPropagation();
        //event.preventDefault();
        $('.contentsReceiveList input[name="chk[]"]').prop('checked' , $('.all_chk').is(':checked') ? true : false);
    });

    $('.delete_btn').on('click' , function (event) {
        event.stopPropagation();
        event.preventDefault();
        var len = $('.contentsReceiveList input[name="chk[]"]').length;
        if(len < 1) alert('선택된 컨텐츠가 없습니다.');
        else {
            if(confirm('내가받은컨텐츠을 삭제하시겠습니까?')){
                var contents_ids = [];

                $('.contentsReceiveList input[name="chk[]"]:checked').each(function(key ,val){
                    contents_ids.push($(this).val());
                });
                var params = {contents_ids :  contents_ids};
                var del_info = $.runsync('/mypage/contentsReceiveDel' , params , 'json' , true);
                if(del_info.code == 200) {
                    $('.contentsReceiveList input[name="chk[]"]:checked').each(function (key, val) {
                        $(this).parents('tr').remove();
                    });
                }else{
                    alert(del_info.message);
                }
            }
        }
    });
});