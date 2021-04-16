<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class LanguageLoader
{
   public function initialize()
   {
		// define('BROWSER_LANGUAGE_TYPE', 'english');
		// define('LANG_TYPE', 'english'); // 언어설정
		// define('I18N_TYPE', 'en'); // 언어설정
		// define('BROWSER_LANGUAGE_FILENAME_TYPE', '_en'); // 언어설정
		// 언어
		
   		if(isset($_GET['lang'])) // 1순위 querystring lang
   		{
			switch(strtoupper($_GET['lang']))
			{
				case "KO-KR":
				case "KR":
				case "KO":
					define('LANG_TYPE', 'KO');
					break;
				case "ZH-CN":
				case "ZH-HK":
				case "ZH-SG":
				case "ZH-TW":
				case "ZH":
					define('LANG_TYPE', 'ZH');
					break;
				default:
					define('LANG_TYPE', 'EN');
					break;
					
			}
   		}
   		else
   		{
   			switch(strtoupper($this->getUserAgentLanguage())) // 2순위 useragent language
   			{
   				case "":
					switch(strtoupper($this->getAcceptLanguage())) // 3순위 AcceptLanguag
					{
						case "KO-KR":
						case "KR":
						case "KO":
							define('LANG_TYPE', 'KO');
							break;
						case "ZH-CN":
						case "ZH-HK":
						case "ZH-SG":
						case "ZH-TW":
						case "ZH":
							define('LANG_TYPE', 'ZH');
							break;
						default:
							define('LANG_TYPE', 'EN');
							break;
					}
   					break;
				case "KO-KR":
				case "KR":
				case "KO":
					define('LANG_TYPE', 'KO');
					break;
				case "ZH-CN":
				case "ZH-HK":
				case "ZH-SG":
				case "ZH-TW":
				case "ZH_CN":
				case "ZH_HK":
				case "ZH_SG":
				case "ZH_TW":
				case "ZH":
					define('LANG_TYPE', 'ZH');
					break;
				default:
					define('LANG_TYPE', 'EN');
					break;
   			}
   		}
   }

    private function getAcceptLanguage()
  	{
  		//ko-KR,ko;q=0.8,en-US;q=0.5,en;q=0.3
		$accept_langs = explode(',', @$_SERVER['HTTP_ACCEPT_LANGUAGE']);

		foreach($accept_langs as $key => $accept_lang)
		{
			$accept_langs[$key] = preg_replace("/\;.*/", "", $accept_lang);
		}

		return (isset($accept_langs[0])) ? $accept_langs[0] : "";
  	}

    private function getUserAgentLanguage()
  	{
  		//language:ko-KR;
  		$user_lang = "";
		if ( preg_match('/language\:([^\;]*);/', @$_SERVER['HTTP_USER_AGENT'], $matches) )
		{
			$user_lang = @$matches[1];
		}

		return $user_lang;
  	}
}