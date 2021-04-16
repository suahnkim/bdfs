<?php

class menu {

    public $list = array();
    public $ACCOUNT_MASTER = 9;
    public $ACCOUNT_P = 1;
    public $ACCOUNT_SP = 2;
    public $ACCOUNT_D = 3;

    public function __construct() {

        $this->__setCategory();
        $this->__setMenu();


    }

/*self::P => "콘텐츠 생성자",
    //self::CP => "콘텐츠 제공자",
self::SP => "스토리지 제공자",
self::D => "유통업자"*/

    private function __setMenu(){
        $this->list = array(
            'MASTER' => array(
                "관리자메뉴"  => array(
                    'v'                                     => '#',
                    'icon'                                => 'fa-tasks',
                    'available_account_type'     => array($this->ACCOUNT_MASTER),
                    'sub'                                => array(
                        '권한승인'  =>  array(
                            'v'                                     => '/auth/request_list',
                            'icon'                                => '',
                            'available_account_type'     => array($this->ACCOUNT_MASTER)
                        ),
                        '회원리스트'  =>  array(
                            'v'                                     => '/auth/user_list',
                            'icon'                                => '',
                            'available_account_type'     => array($this->ACCOUNT_MASTER)
                        ),
                    ),
                ),
            ),
            'CP'=>array(
                '등록한 콘텐츠' => array(
                    'v'                                     => '/contents/lists/P',
                    'icon'                                => 'fa-upload',
                    'available_account_type'     => array($this->ACCOUNT_MASTER , $this->ACCOUNT_P),
                    'sub'                                => array(),
                ),
               '상품정보등록된 콘텐츠'=>array(
                    'v'                                     =>  '/contents/plist',
                    'icon'                                => 'fa-file-signature',
                    'available_account_type'     => array($this->ACCOUNT_MASTER , $this->ACCOUNT_P),
                    'sub'                                => array(),
                ) ,
               /* '콘텐츠 판매현황'=>array(
                    'v'                                     =>  '/contents/list',
                    'icon'                                => 'fa-share-square',
                    'available_account_type'     => array($this->ACCOUNT_MASTER , $this->ACCOUNT_P),
                    'sub'                                => array(),
                ) ,
                '저작권료 적립 내역'=>array(
                    'v'                                     =>  '/contents/list',
                    'icon'                                => '',
                    'available_account_type'     => array($this->ACCOUNT_MASTER , $this->ACCOUNT_P),
                    'sub'                                => array(),
                ) */
            ),
            //"CP"=>array(),
            "SP"=>array(),
            "D"=>array(
                '전체 콘텐츠' => array(
                    'v'                                     => '/contents/lists_api_D',
                    'icon'                                => 'fa-folder-open',
                    'available_account_type'     => array($this->ACCOUNT_MASTER , $this->ACCOUNT_D),
                    'sub'                                => array(),
                ),
                '유통중인 콘텐츠' => array(
                    'v'                                     => '/contents/lists/D?list_type=sell',
                    'icon'                                => 'fa-share-square',
                    'available_account_type'     => array($this->ACCOUNT_MASTER , $this->ACCOUNT_D),
                    'sub'                                => array(),
                ),
                /*
                '콘텐츠 판매현황' => array(
                    'v'                                     => '/contents/defaultPage',
                    'icon'                                =>'fa-chart-bari',
                    'available_account_type'     => array($this->ACCOUNT_MASTER , $this->ACCOUNT_D),
                    'sub'                                => array(),
                ),
                */
                'WEI 적립내역' => array(
                    'v'                                     => '/contents/defaultPage',
                    'icon'                                => 'fa-coins',
                    'available_account_type'     => array($this->ACCOUNT_MASTER , $this->ACCOUNT_D),
                    'sub'                                => array(),
                ),
            )
        );
    }


    private function __setCategory(){
        $this->categoty_list = array(
            'C01'=>'게임',
            'C02'=>'음악/댄스',
            'C03'=>'스포츠',
            'C04'=>'음식',
            'C05'=>'동물',
            'C06'=>'여행',
            'C07'=>'육아/키즈',
            'C08'=>'뷰티/미용',
            'C09'=>'교육/강의',
            'C10'=>'생활/정보',
            'C11'=>'경제/금융',
            'C12'=>'시사/정치',
            'C13'=>'기타'
        );
    }

}
?>