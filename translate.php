<?php 
function translate($str = null,$total = false)
	{
		$translatetable = array('kalender' => 'events',
					'fotos' => 'images',
					'afbeeldingen' => 'images',
					'activiteiten' => 'events',
					'paginas' => 'pages',
					'standaard' => 'default',
					'voegtoe' => 'add',
					'verwijder' => 'delete',
					'bewerk' => 'edit',
					'bekijk' => 'view',
					'overzicht' => 'index',
					'usersa'=>'users',
					'toekomst' => 'future',
					'verleden' => 'past',
					'nu' => 'now',
					'vragenenantwoorden' => 'faq'
								);
		if($str)
		{
			if($total)
			{ 
				$old = explode('/',$str);
				$lastone = end($old);
				if(empty($lastone)) array_pop($old);
				$new = array();
								
				/* translate each part or leave untranslated part */

				for($i = 0 ; $i <sizeof($old) ; $i++)
				{
					$new[$i] = translate($old[$i]);
				}

				
				/* construct the translated url.  this also adds a trailing "/" even if it wasn't in the original */
				$new_url="";
				foreach($new as $n)
				{
					$new_url .= $n."/";
				}
				
				return $new_url;
			}
			else
			{
				foreach ($translatetable as $orig => $new)
				{
					if($str == $orig) $str = $new;
				}
				return $str;
			}
		}
	}
?>