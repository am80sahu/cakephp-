<?php

App::uses('Component', 'Controller');

class P28nComponent extends Component {
    var $components = array('Session', 'Cookie');

	var $acceptedLanguages = array ("eng","afr","ara","ar_ae","ar_bh","ar_dz","ar_eg","ar_iq","ar_jo","ar_kw","ar_lb","ar_ly","ar_ma","ar_om","ar_qa","ar_sa","ar_sy","ar_tn","ar_ye","bel","bul","bos","cat","cze","dan","deu","de_at","de_ch","de_de","de_li","de_lu","gre","eng","en_au","en_bz","en_ca","en_ie","en_jm","en_nz","en_tt","en_us","en_za","spa","es_ar","es_bo","es_cl","es_co","es_cr","es_do","es_ec","es_es","es_gt","es_hn","es_mx","es_ni","es_pa","es_pe","es_pr","es_py","es_sv","es_uy","es_ve","est","baq","per","fin","fao","fre","fr_be","fr_ca","fr_ch","fr_fr","fr_lu","gle","gla","gd_ie","glg","heb","hin","hrv","hun","hye","ind","ind","ice","ita","it_ch","jpn","kor","ko_kp","ko_kr","koi8_r","lit","lav","mk","mk_mk","may","mlt","dut","nob","nl_be","nno","nor","pol","pol","por","pt_br","roh","rum","rus","ro_mo","ru_mo","wen","slo","slv","alb","scc","swe","sv_fi","sx","smi","tha","tsn","tur","tso","ukr","urd","ven","vie","xho","yid","chi","zh_cn","zh_hk","zh_sg","zh_tw","zul");

	
	function startup(Controller $controller) {	
	

	if (!$this->Session->check('Config.language')) {
			
            $this->change(($this->Cookie->read('lang') ? $this->Cookie->read('lang') : DEFAULT_LANGUAGE));
        } 
			
	}

	function change($lang = null) {
      
	//$usedlangflag=array('eng'=>'uk.png','es_es'=>'spain.png','rum'=>'romania.png','gre'=>'greece.png','hun'=>'hungary.png','gle'=>'ireland.jpg','ita'=>'italia.png','dut'=>'netherlands.png','por'=>'portugal.png','hrv'=>'deutschland.jpg','dan'=>'denmark.png','cze'=>'czech_republic.png','est'=>'estonia.png','fre'=>'france.jpg','deu'=>'germany.jpg');

			if (!empty($lang)) {			
			
			/*$this->Session->write('Brand'.BrandId, $lang);*/
			$this->Session->write('Config.language', $lang);
			/*$this->Session->write('Config.country_code',$country_code);*/
			//$this->Session->write('langflag',$usedlangflag[$lang]);			
			$this->Cookie->write('lang', $lang, null);

		}
	}
/*To detect current operating system the user used*/
function CurrOS(){
$OSList = array
      (
              // Match user agent string with operating systems
              'Windows 3.11' => 'Win16',

              'Windows 95' => '(Windows 95)|(Win95)|(Windows_95)',

              'Windows 98' => '(Windows 98)|(Win98)',

              'Windows 2000' => '(Windows NT 5.0)|(Windows 2000)',

              'Windows XP' => '(Windows NT 5.1)|(Windows XP)',

              'Windows Server 2003' => '(Windows NT 5.2)',

              'Windows Vista' => '(Windows NT 6.0)',

              'Windows 7' => '(Windows NT 7.0)',

              'Windows NT 4.0' => '(Windows NT 4.0)|(WinNT4.0)|(WinNT)|(Windows NT)',

              'Windows ME' => 'Windows ME',

              'Open BSD' => 'OpenBSD',

              'Sun OS' => 'SunOS',

              'Linux' => '(Linux)|(X11)',

              'Mac OS' => '(Mac_PowerPC)|(Macintosh)',

              'QNX' => 'QNX',

              'BeOS' => 'BeOS',

              'OS/2' => 'OS/2',

              'Search Bot'=>'(nuhk)|(Googlebot)|(Yammybot)|(Openbot)|(Slurp)|(MSNBot)|(Ask Jeeves/Teoma)|(ia_archiver)'

      );
      // Loop through the array of user agents and matching operating systems
      foreach($OSList as $CurrOS=>$Match)
      {
              // Find a match
              if (eregi($Match, $_SERVER['HTTP_USER_AGENT']))
              {
                      // We found the correct match
                      break;
              }
      }
return $CurrOS;
}

function get_languages( $feature, $spare='' )
{
	// get the languages
	$a_languages = $this->languages();
	$index = '';
	$complete = '';
	$found = false;// set to default value
	//prepare user language array
	$user_languages = array();

	//$_SERVER["HTTP_ACCEPT_LANGUAGE"] = 'fr-ch;q=0.3';

	//check to see if language is set
	if ( isset( $_SERVER["HTTP_ACCEPT_LANGUAGE"] ) )
	{
		$languages = strtolower( $_SERVER["HTTP_ACCEPT_LANGUAGE"] );
	
		// need to remove spaces from strings to avoid error
		$languages = str_replace( ' ', '', $languages );
		$languages = explode( ",", $languages );
		//$languages = explode( ",", $test);// this is for testing purposes only

		foreach ( $languages as $language_list )
		{
			// pull out the language, place languages into array of full and primary
			// string structure:
			$temp_array = array();
			// slice out the part before ; on first step, the part before - on second, place into array
			$temp_array[0] = substr( $language_list, 0, strcspn( $language_list, ';' ) );//full language
			$temp_array[1] = substr( $language_list, 0, 2 );// cut out primary language
			//place this array into main $user_languages language array
			$user_languages[] = $temp_array;
		}

		//start going through each one
		for ( $i = 0; $i < count( $user_languages ); $i++ )
		{
			foreach ( $a_languages as $index => $complete )
			{
				if ( $index == $user_languages[$i][0] )
				{
					// complete language, like english (canada)
					$user_languages[$i][2] = $complete;
					// extract working language, like english
					$user_languages[$i][3] = substr( $complete, 0, strcspn( $complete, ' (' ) );
				}
			}

		}
	}
	else// if no languages found
	{
		$user_languages[0] = array( '','','','' ); //return blank array.
	}

	if ( $feature == 'data' )
	{
		return $user_languages;
	}

	// this is just a sample, replace target language and file names with your own.
	elseif ( $feature == 'header' )
	{
		switch ( $user_languages[0][1] )// get default primary language, the first one in array that is
		{
			case 'en':
				$location = 'english.php';
				$found = true;
				break;
			case 'sp':
				$location = 'spanish.php';
				$found = true;
				break;
			default:
				break;
		}
		if ( $found )
		{
			header("Location: $location");
		}
		else// make sure you have a default page to send them to
		{
			header("Location: default.php");
		}
	}
}

function languages()
{
// pack abbreviation/language array
// important note: you must have the default language as the last item in each major language, after all the
// en-ca type entries, so en would be last in that case
	$a_languages = array(
	'af' => 'Afrikaans',
	'sq' => 'Albanian',
	'ar-dz' => 'Arabic (Algeria)',
	'ar-bh' => 'Arabic (Bahrain)',
	'ar-eg' => 'Arabic (Egypt)',
	'ar-iq' => 'Arabic (Iraq)',
	'ar-jo' => 'Arabic (Jordan)',
	'ar-kw' => 'Arabic (Kuwait)',
	'ar-lb' => 'Arabic (Lebanon)',
	'ar-ly' => 'Arabic (libya)',
	'ar-ma' => 'Arabic (Morocco)',
	'ar-om' => 'Arabic (Oman)',
	'ar-qa' => 'Arabic (Qatar)',
	'ar-sa' => 'Arabic (Saudi Arabia)',
	'ar-sy' => 'Arabic (Syria)',
	'ar-tn' => 'Arabic (Tunisia)',
	'ar-ae' => 'Arabic (U.A.E.)',
	'ar-ye' => 'Arabic (Yemen)',
	'ar' => 'Arabic',
	'hy' => 'Armenian',
	'as' => 'Assamese',
	'az' => 'Azeri',
	'eu' => 'Basque',
	'be' => 'Belarusian',
	'bn' => 'Bengali',
	'bg' => 'Bulgarian',
	'ca' => 'Catalan',
	'zh-cn' => 'Chinese (China)',
	'zh-hk' => 'Chinese (Hong Kong SAR)',
	'zh-mo' => 'Chinese (Macau SAR)',
	'zh-sg' => 'Chinese (Singapore)',
	'zh-tw' => 'Chinese (Taiwan)',
	'zh' => 'Chinese',
	'hr' => 'Croatian',
	'cs' => 'Czech',
	'da' => 'Danish',
	'div' => 'Divehi',
	'nl-be' => 'Dutch (Belgium)',
	'nl' => 'Dutch (Netherlands)',
	'en-au' => 'English (Australia)',
	'en-bz' => 'English (Belize)',
	'en-ca' => 'English (Canada)',
	'en-ie' => 'English (Ireland)',
	'en-jm' => 'English (Jamaica)',
	'en-nz' => 'English (New Zealand)',
	'en-ph' => 'English (Philippines)',
	'en-za' => 'English (South Africa)',
	'en-tt' => 'English (Trinidad)',
	'en-gb' => 'English (British)',
	'en-us' => 'English (United States)',
	'en-zw' => 'English (Zimbabwe)',
	'en' => 'English',
	'us' => 'English (United States)',
	'et' => 'Estonian',
	'fo' => 'Faeroese',
	'fa' => 'Farsi',
	'fi' => 'Finnish',
	'fr-be' => 'French (Belgium)',
	'fr-ca' => 'French (Canada)',
	'fr-lu' => 'French (Luxembourg)',
	'fr-mc' => 'French (Monaco)',
	'fr-ch' => 'French (Switzerland)',
	'fr' => 'French (France)',
	'mk' => 'FYRO Macedonian',
	'gd' => 'Gaelic',
	'ka' => 'Georgian',
	'de-at' => 'German (Austria)',
	'de-li' => 'German (Liechtenstein)',
	'de-lu' => 'German (Luxembourg)',
	'de-ch' => 'German (Switzerland)',
	'de' => 'German (Germany)',
	'el' => 'Greek',
	'gu' => 'Gujarati',
	'he' => 'Hebrew',
	'hi' => 'Hindi',
	'hu' => 'Hungarian',
	'is' => 'Icelandic',
	'id' => 'Indonesian',
	'it-ch' => 'Italian (Switzerland)',
	'it' => 'Italian (Italy)',
	'ja' => 'Japanese',
	'kn' => 'Kannada',
	'kk' => 'Kazakh',
	'kok' => 'Konkani',
	'ko' => 'Korean',
	'kz' => 'Kyrgyz',
	'lv' => 'Latvian',
	'lt' => 'Lithuanian',
	'ms' => 'Malay',
	'ml' => 'Malayalam',
	'mt' => 'Maltese',
	'mr' => 'Marathi',
	'mn' => 'Mongolian (Cyrillic)',
	'ne' => 'Nepali (India)',
	'nb-no' => 'Norwegian (Bokmal)',
	'nn-no' => 'Norwegian (Nynorsk)',
	'no' => 'Norwegian (Bokmal)',
	'or' => 'Oriya',
	'pl' => 'Polish',
	'pt-br' => 'Portuguese (Brazil)',
	'pt' => 'Portuguese (Portugal)',
	'pa' => 'Punjabi',
	'rm' => 'Rhaeto-Romanic',
	'ro-md' => 'Romanian (Moldova)',
	'ro' => 'Romanian',
	'ru-md' => 'Russian (Moldova)',
	'ru' => 'Russian',
	'sa' => 'Sanskrit',
	'sr' => 'Serbian',
	'sk' => 'Slovak',
	'ls' => 'Slovenian',
	'sb' => 'Sorbian',
	'es-ar' => 'Spanish (Argentina)',
	'es-bo' => 'Spanish (Bolivia)',
	'es-cl' => 'Spanish (Chile)',
	'es-co' => 'Spanish (Colombia)',
	'es-cr' => 'Spanish (Costa Rica)',
	'es-do' => 'Spanish (Dominican Republic)',
	'es-ec' => 'Spanish (Ecuador)',
	'es-sv' => 'Spanish (El Salvador)',
	'es-gt' => 'Spanish (Guatemala)',
	'es-hn' => 'Spanish (Honduras)',
	'es-mx' => 'Spanish (Mexico)',
	'es-ni' => 'Spanish (Nicaragua)',
	'es-pa' => 'Spanish (Panama)',
	'es-py' => 'Spanish (Paraguay)',
	'es-pe' => 'Spanish (Peru)',
	'es-pr' => 'Spanish (Puerto Rico)',
	'es-us' => 'Spanish (United States)',
	'es-uy' => 'Spanish (Uruguay)',
	'es-ve' => 'Spanish (Venezuela)',
	'es' => 'Spanish (Traditional Sort)',
	'sx' => 'Sutu',
	'sw' => 'Swahili',
	'sv-fi' => 'Swedish (Finland)',
	'sv' => 'Swedish',
	'syr' => 'Syriac',
	'ta' => 'Tamil',
	'tt' => 'Tatar',
	'te' => 'Telugu',
	'th' => 'Thai',
	'ts' => 'Tsonga',
	'tn' => 'Tswana',
	'tr' => 'Turkish',
	'uk' => 'Ukrainian',
	'ur' => 'Urdu',
	'uz' => 'Uzbek',
	'vi' => 'Vietnamese',
	'xh' => 'Xhosa',
	'yi' => 'Yiddish',
	'zu' => 'Zulu' );

	return $a_languages;
}

/**
 * Returns or creates an instance of the LocaleComponent.
 *
 * @return LocaleComponent instance
 */
/**
 * Return a singleton instance of the Locale Component.
 *
 * @return Locale instance
 * @access public
 */
   function &getInstance()
   {
      static $instance= array();
      if (!$instance)
      {
         $instance[0] =& new LocaleComponent();
      }
      return $instance[0];
   }

/**
 * Change the language code for all messages. Only permits changes to values which
 * are in the acceptedLanguages parameter. This message is not intended to receive
 * user defined input.
 *
 * @param string $langcode 5 character language code
 * @return true if code changed, false if not.
 */
    function changeCode ($langcode=NULL) {
        if (!in_array($langcode,$this->acceptedLanguages))
        {
            $msg = __("Invalid language code",Array($langcode));
            $this->Session->setFlash($msg);
            return false;
        }
        else
        {
            $this->Session->del("Lang.Menu");
            $this->Session->del("Lang.Messages");
            $this->Session->write("Lang.Code", $langcode);
            return true;
        }
     }

/**
 * returns a translated string. This method will return the requested message in the
 * language requested if it exists; if not the message in the default language if it exists
 * and finally the message $id if no message is defined.
 *
 * @param string $id the message or message id
 * @param array $MessageArgs variables to be substitued into the message defenition
 * @param int $capitalize how should the message be capitalized
 *      0 = no change
 *      1 = first letter of first word
 *      2 = fist character of all words
 *      Assumed that the message is stored lowercase except when upper case is required
 *      Such as with objects e.g. in German "der Mann".
 * @param int $punctuate how should the message be punctuated
 *      0 = .
 *      1 = !
 *      2 = ?
 * @param string $Code override for the language to be used.
 * @return string output message.
 */
	function getString( $id, $MessageArgs=NULL, $capitalize=1, $punctuate=0 ,$Code=NULL)
    {
        $Code = (!$Code)?$this->_getCode():$Code;
        $this->_populateMessages ($Code);
	    $this->_populateMessages ($this->acceptedLanguages[0]);

        if( isset($id, $this->_messages[$Code][$id]))
         {
              if ($MessageArgs)
              {
                  $string = vsprintf ($this->_messages[$Code][ $id ], $MessageArgs);
              }
              else
              {
		          $string = $this->_messages[$Code][ $id ];
              }
	    }
        elseif ( isset($id, $this->_messages[$this->acceptedLanguages[0]][$id]))
        {
              if ($MessageArgs)
              {
                  $string = vsprintf ($this->_messages[$this->acceptedLanguages[0]][ $id ], $MessageArgs);
              }
              else
              {
		          $string = $this->_messages[$this->acceptedLanguages[0]][ $id ];
              }
	    }
        else
        {
	    	$string = $id;
	    }

	    switch ($capitalize)
        {
          case 0:
            break;
          case 1:
            $string = ucfirst($string);
            break;
          case 2:
            $string = ucwords($string);
            break;
        }

	    switch ($punctuate)
        {
          case 0:
            break;
          case 1:
            if ($Code=='es_es')
            {
                $string = "".$string.".";
            } else
            {
                $string = $string.".";
            }
            break;
          case 2:
            if ($Code=='es_es')
            {
                $string = "¡".$string."!";
            } else
            {
                $string = $string."!";
            }
            break;
          case 3:
            if ($Code=='es_es')
            {
                $string = "¿".$string."?";
            } else
            {
                $string = $string."?";
            }
            break;
        }
 		return $string;
	}

/**
 * returns the language code for the session. If a language code has not been defined
 * the code determined from the ethod  _checkBrowserLanguage is returned
 * @return string 5 character language code
 */
    function _getCode () {
      if (isset($this->Session))
      {
       return $this->Session->read("Lang.Code")?$this->Session->read("Lang.Code"): $this->_checkBrowserLanguage();
      }
      else
      {
        return isset($_SESSION['Lang']['Code'])?$_SESSION['Lang']['Code']:$this->_checkBrowserLanguage();
      }
    }

/**
 * stores the passed code in the session.
 */
    function _setCode ($langcode=NULL)
    {
        $this->Session->write("Lang.Code", $langcode);
    }

/**
 * This method will return the most appropriate language defined in the acceptedLanguages list
 * based upon the browser language settings. If no language matches the acceptedLanguages
 * list, the first item of the acceptedLanguages list (the default language)is returned.
 * This method isn't optimised and needs improving.
 *
 * @return string 5 character language code
 */
    function _checkBrowserLanguage () {
      	if (isset($_SERVER['HTTP_ACCEPT_LANGUAGE']))
      	{	    
	        $Languages = explode(",", $_SERVER['HTTP_ACCEPT_LANGUAGE']);
	        $SLanguages = array();
	        foreach ($Languages as $Key=> $Language) {
	            $Language = ereg_replace("-","_",$Language);
	            $Language = explode(";", $Language);
	            if (isset($Language[1])) {
	                $Priority = explode("q=", $Language[1]);
	                $Priority=$Priority[1];
	            }
	            else
	            {
	                $Priority ="1.0";
	            }
	            $SLanguages[] = array('priority' => $Priority, 'language' => $Language[0]);
	        }
	        // Obtain a list of columns
	        foreach ($SLanguages as $key => $row) {
	            $priority[$key]  = $row['priority'];
	            $language[$key] = $row['language'];
	        }
	        // Sort the Slanguges with priority descending, languagecode ascending
	        // Add $Languages as the last parameter, to sort by the common key
	        array_multisort($priority, SORT_DESC, $language, SORT_ASC, $SLanguages);
	
	        $ALangString = implode(";",$this->acceptedLanguages);
	        foreach ($SLanguages as $A) {
	            $key = array_search($A['language'], $this->acceptedLanguages);
	            if ($key===FALSE) {
	                $GenericLanguage = explode("_", $A['language']);
	                $pos1 = strpos($ALangString, $GenericLanguage[0]);
	                if (is_numeric($pos1)) {
	                    $key = $pos1/6;
	                }
	            }
	            if (is_numeric($key)&&(!isset($Code))) {
	                $Code = $this->acceptedLanguages[$key];
	            }
	        }
	        if (!isset($Code)) 
			{
	            $Code = $this->acceptedLanguages[0];
	        }
        }
        else
        {
		  $Code = $this->acceptedLanguages[0];
		}
        return $Code;
    }

/**
 * set the _message variable for the specified language code
 */
	function _populateMessages($langcode)
    {
        if (isset($this->Session))
        {
            if ($this->Session->check('Lang.Messages'))
            {
                $sessionMessages = $this->Session->read("Lang.Messages");
            }
            else
            {
                $sessionMessages = NULL;
            }
        }
        else
        {
            $sessionMessages = isset($_SESSION['Lang']["Messages"])?$_SESSION['Lang']["Messages"]:NULL;
        }

        if (isset($sessionMessages[$langcode]))
        {
            $this->_messages[$langcode] = $sessionMessages[$langcode];
        }
        else
        {
			$this->_loadLocaleFile($langcode);
			if ($langcode<>$this->acceptedLanguages[0])
			{
                $this->_loadLocaleFile($this->acceptedLanguages[0]);
            }
        }
   }

/**
 * Load the language file for the specified language code
 *
 * @return true if file loaded, false if not.
 */
	function _loadLocaleFile($langcode) {
		$fileName = dirname(__FILE__).DS.$this->_messageFolder.DS.$langcode . ".php";
        if (file_exists($fileName))
        {
            include( $fileName );
    		$this->_messages[$langcode] = $messages;
            return true;
        }
        else
        {
    		$this->_messages[$langcode] = NULL;
            return false;
     
        }
	}

}
?>