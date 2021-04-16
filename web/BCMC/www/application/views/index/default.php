<script>
    $(document).ready(function () {
        $('.btn-logout').on('click' , function (event) {
            $.onchain_proc('account/logout','',logout_call_back, 'on');
        });
    });

    function logout_call_back(data){
        if(data.resultCode == 0){
            document.location.replace('/user/logout');
        }else{
            alert(data.resultMessage);
        }
    }
</script>