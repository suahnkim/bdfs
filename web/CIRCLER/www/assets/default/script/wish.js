$(document).ready(function () {
    var ll = new LazyLoad({
        threshold: 0
    });
    $('.wish_btnbox .all_del_btn').on('click' , function (event) {
        event.stopPropagation();
        event.preventDefault();

        $('.wish_btnbox .off').fadeOut(100, function () {
            $('.wish_btnbox .on').fadeIn(100);
            $('.wishlist li.pic .selcetLine').fadeIn(100);
            $('.wishlist').removeClass('contentsList');
        });
    });

    $('.wish_btnbox .all_sel_btn').on('click' ,function (event) {
        event.stopPropagation();
        event.preventDefault();
        $('.wishlist li.pic .delcheck').prop('checked' , true);
    });

    $('.wish_btnbox .cancle_btn').on('click' , function (event) {
        event.stopPropagation();
        event.preventDefault();

        $('.wish_btnbox .on').fadeOut(100, function () {
            $('.wish_btnbox .off').fadeIn(100);
            $('.wishlist li.pic .selcetLine').fadeOut(100);
            $('.wishlist li.pic .delcheck').prop('checked' , false);
            $('.wishlist').addClass('contentsList');
        });
    });

    $('.wish_btnbox .confirm_btn').on('click' , function (event) {
        event.stopPropagation();
        event.preventDefault();

        var len = $('.wishlist li.pic .delcheck:checked').length;
        if(len > 0){
            var contents_ids = [];
            $('.wishlist li.pic .delcheck:checked').each(function () {
                contents_ids.push($(this).val());
                //$(this).parents('li.pic').remove();
            });
            var params = {contents_ids : contents_ids};
            var zzim_info = $.runsync('/mypage/zzimDel', params , 'json' , true);
            if(zzim_info.code == 200){
                $('.wishlist li.pic .delcheck:checked').each(function () {
                    //contents_ids.push($(this).val());
                    $(this).parents('li.pic').remove();
                });
            }else{
                alert(zzim_info.message);
            }
        }else{
            alert('선택된 콘텐츠가  없습니다.');
        }

    });
});