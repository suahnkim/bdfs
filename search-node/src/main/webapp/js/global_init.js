//list
var CONTENT_TYPE = [
	{"code" : "video", "name" : "Video"},
	{"code" : "audio", "name" : "Audio"},
	{"code" : "ebook", "name" : "E-Book"},
	];
var CONTENT_TYPE_MAP = {
	"video" : "Video",
	"audio" : "Audio",
	"ebook" : "Book",
	};

var GENRE = [
	{"code" : "C01", "name" : "게임"},
	{"code" : "C02", "name" : "음악/댄스"},
	{"code" : "C03", "name" : "스포츠"},
	{"code" : "C04", "name" : "음식"},
	{"code" : "C05", "name" : "동물"},
	{"code" : "C06", "name" : "여행"},
	{"code" : "C07", "name" : "육아/키즈"},
	{"code" : "C08", "name" : "뷰티/미용"},
	{"code" : "C09", "name" : "교육/강의"},
	{"code" : "C10", "name" : "생활/정보"},
	{"code" : "C11", "name" : "경제/금융"},
	{"code" : "C12", "name" : "시사/정치"},
	{"code" : "C13", "name" : "기타"},
	
	{"code" : "fear",      "name" : "Fear"},
	{"code" : "drama",     "name" : "Drama"},
	{"code" : "animation", "name" : "Animation"},
	{"code" : "action",    "name" : "Action"},
];

var GENRE_MAP = {
	"C01" : "게임",
	"C02" : "음악/댄스",
	"C03" : "스포츠",
	"C04" : "음식",
	"C05" : "동물",
	"C06" : "여행",
	"C07" : "육아/키즈",
	"C08" : "뷰티/미용",
	"C09" : "교육/강의",
	"C10" : "생활/정보",
	"C11" : "경제/금융",
	"C12" : "시사/정치",
	"C13" : "기타",	
	"fear" : "Fear",
	"drama" : "Drama",
	"animation" : "Animation",
	"action" : "Action",
};


function numberWithCommas(x) {
    return x.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
}
