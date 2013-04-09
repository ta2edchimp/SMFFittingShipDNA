array(
	'tag' => 'eft',
	'type' => 'unparsed_content',
	'content' => '$1',
	// !!! Maybe this can be simplified?
	'validate' => create_function('&$tag, &$data, $disabled', '
		global $smcFunc;
		$export = $data;
		
		$data = preg_replace(\'#<br\s*/?>#i\', "\n",$data);
		$fitdetails = explode("\n",trim($data));

		foreach($fitdetails as $key=>$row){
		if($key == \'0\'){
			$titlebits = explode(",",preg_replace("/(\[|\]|)/","",$row));
			$shipType = $titlebits[0];
			$name = trim($titlebits[1]);
			continue;
		}
			$fitting_array[] = $row;
		}
		
		//Start fitting
		$fitting_output = \'<div id="fittitle"><h3>\'.$name.\'</h3><h4>\'.$shipType.\'</h4></div>\';
		$fitting_output .= \'<div id="fitting_container">\';
		$fitting_output .= \'<div class="fitting_tabs"><ul class="fit-tabs"><li class="fit-tab" onclick="chooseTab(this,\\\'loadout\\\');">Loadout</li><li class="fit-tab" onclick="chooseTab(this,\\\'export\\\');">EFT Export</li></ul><div style="clear:both;"></div></div>\';
		$fitting_output .= \'<div id="fittext" style="display:none;">\' .$export . \'</div>\';
		$fitting_output .= \'<div title="fitting" id="fitting">\';
		
		//Fitting window
		$fitting_output .= \'<div id="fittingwindow"><img border="0" alt="" src="images/fitting/fitting2.png"></div>\';
		
		$sql = \'SELECT * FROM EFTShips WHERE typeName="\'.$shipType.\'"\';
		$result = $smcFunc[\'db_query\'](\'\',$sql);
		$shipdetails = $smcFunc[\'db_fetch_assoc\']($result);
		$fitting_output .= \'<div id="shipicon"><img border="0" alt="" title="\'.$shipType.\'" src="http://image.eveonline.com/InventoryType/\'.$shipdetails["TypeID"].\'_64.png"></div>\';
		
		$sql = \'SELECT DISTINCT location FROM EFTmodules\';
		$result = $smcFunc[\'db_query\'](\'\',$sql);
		while($row = $smcFunc[\'db_fetch_assoc\']($result))
		{
			$position[$row[\'location\']] = 1;
		}
		
		//Produce fit display
		foreach($fitting_array as $itemname){
			
			//ignore blank lines
			if($itemname ==\'\')
				continue;
			
			if(strpos($itemname,\',\')){
				$details = explode(\',\',$itemname);
				$itemname = $details[0];
			}
			
			//get the info about the module
			$sql = \'SELECT * FROM EFTmodules where TypeName = "\' . $itemname . \'"\';
				
			$result = $smcFunc[\'db_query\'](\'\',$sql);
			$row = $smcFunc[\'db_fetch_assoc\']($result);

			$fitting_output .= \'<div id="\'.$row[\'location\'].$position[$row[\'location\']].\'"><img border="0" title="\'.$itemname.\'" src="http://image.eveonline.com/Type/\'.$row[\'TypeID\'].\'_32.png" /></div>\';
			
			if(!empty($details[1])){
				$charges[$row[\'location\']][$position[$row[\'location\']]] = trim($details[1]);
				unset($details);
			}
			
			//increment the position
			$position[$row[\'location\']]++;
			
		}
		
		if(is_array($charges)){
			foreach($charges as $slot=>$positions){
				foreach($positions as $position=>$itemname){
					if(!empty($itemname))
					{
						$sql = \'SELECT typeID FROM EFTCharges where TypeName = "\' . $itemname . \'"\';
						$result = $smcFunc[\'db_query\'](\'\',$sql);
						$row = $smcFunc[\'db_fetch_assoc\']($result);
						$fitting_output .= \'<div id="\'.$slot.\'charge\'.($position).\'"><img border="0" title="\'.$itemname.\'" src="http://image.eveonline.com/InventoryType/\'.$row[\'typeID\'].\'_32.png"></div>\';
					}
				}
			}
		}
		
		$sql = \'SELECT DISTINCT location FROM EFTmodules\';
		$result = $smcFunc[\'db_query\'](\'\',$sql);
		while($row = $smcFunc[\'db_fetch_assoc\']($result))
		{
			for($i = ($position[$row[\'location\']]);$i < $shipDetails[$row[\'location\']];$i++){
				$fitting_output .= \'<div id="\'.$row[\'location\'].($i+1).\'"><img border="0" title="Empty \'.ucfirst($row[\'location\']).\' Slot" src="images/fitting/\'.$row[\'location\'].\'.png"></div>\';
			}
		}
		
		//End fitting
		$fitting_output .= \'</div></div>\';
		
		
		$data = $fitting_output;
	'),
	'block_level' => true,
),