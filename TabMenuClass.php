<?php 
class TabMenuClass{
	public function getTabMenu($menuId){
		global $conn;

		$menuIds= $menuId;
		$menuArr = array();
		$menuSql = "SELECT `Cat`,`MenuId` FROM `Menu` WHERE `MenuId` in ($menuIds)";
		$menuQuery=mysqli_query($conn,$menuSql);
		$catArr = array();
		$zeroArr = array();
		while($menuRow = mysqli_fetch_assoc($menuQuery)){
			$cat = $menuRow["Cat"];
			if(!in_array($cat, $catArr) && ($cat != null || $cat != '')){
				array_push($catArr,$cat);
			}
			else{
				//array_push($zeroArr,$menuRow["MenuId"]);
			}
		}

		$resultArr = array();
		for($i = 0; $i < count($catArr); $i++){
			$subCatSql = "SELECT `Sub`,`Caption` FROM `Menu` WHERE `MenuId` in ($menuIds) and `Cat` = '$catArr[$i]'";
			$subCatQuery=mysqli_query($conn,$subCatSql);
			$levelType = "";
			while($subCatRow = mysqli_fetch_assoc($subCatQuery)){
				$sub = $subCatRow["Sub"];
				$caption = $subCatRow["Caption"];
				if($sub == '' && $caption == ''){
					// first level
					$levelType = 'FIRST';
				}
				else if($sub != '' && $caption == ''){
					// second level
					$levelType = 'SECOND';
				}
				else if($sub != '' && $caption != ''){
					// third level
					$levelType = 'THIRD';
				}
			}

			if($levelType == "FIRST"){
				$subCatSqlll = "SELECT * FROM `Menu` WHERE `MenuId` in ($menuIds) and `Cat` = '$catArr[$i]'";
				$subCatQueryyy=mysqli_query($conn,$subCatSqlll);
				while($subCatRowww = mysqli_fetch_assoc($subCatQueryyy)){
					$aa = $subCatRowww["MenuId"];
					$bb = $subCatRowww["Cat"];
					$ee = $subCatRowww["CheckpointId"];
					$ff = $subCatRowww["Active"];
					$gg = $subCatRowww["Icons"];
					$colors = $subCatRowww["Colors"];
					$hh = $subCatRowww["GeoFence"];
					$ii = $subCatRowww["Verifier"];
					$jj = $subCatRowww["Approver"];
					$msgbox = $subCatRowww["msgbox"];

					$hhExplode = explode(":", $hh);
					$GeoCoordinate = $hhExplode[0];
					if($GeoCoordinate == ""){
						$GeoCoordinate = null;
					}
					$GeoFence = $hhExplode[1];
					if($GeoFence == ""){
						$GeoFence = null;
					}


					$iconExplode = explode(",", $gg);
					$categoryIcon = $iconExplode[0];

					$colorsExplode = explode(":", $colors);
					$catColors = $colorsExplode[0];
					$catColorsExplode = explode(",", $catColors);
					$bgColor = $catColorsExplode[0];
					$fontColor = $catColorsExplode[1];

					$json1 = array();
					$json1 = array(
						'menuId' => $aa,
						'Caption' => $catArr[$i],
						'Icon' => $categoryIcon, 
						'bgColor' => $bgColor,
						'fontColor' => $fontColor,
						'subCategoryList' => array(),
						'checkpointId' => $ee,
						'active' => $ff,
						'Editable' => "",
						'GeoCoordinate' => $GeoCoordinate,
						'GeoFence' => $GeoFence,
						'verifier' => $ii,
						'approver' => $jj,
						'msgbox' => $msgbox
					);
					array_push($resultArr,$json1);
				}
			}
			else if($levelType == "SECOND"){
				$subCatSqll = "SELECT * FROM `Menu` WHERE `MenuId` in ($menuIds) and `Cat` = '$catArr[$i]'";
				$subCatQueryy=mysqli_query($conn,$subCatSqll);
				$categoryIcon = "";
				$resultSubCatArr = array();
				$subCatArr = array();
				while($subCatRoww = mysqli_fetch_assoc($subCatQueryy)){
					$subb = $subCatRoww["Sub"];
					if(!in_array($subb, $subCatArr) && ($subb != null || $subb != '') ){
						$aa = $subCatRoww["MenuId"];
						$bb = $subCatRoww["Sub"];
						$ee = $subCatRoww["CheckpointId"];
						$ff = $subCatRoww["Active"];
						$gg = $subCatRoww["Icons"];
						$colors = $subCatRoww["Colors"];
						$hh = $subCatRoww["GeoFence"];
						$ii = $subCatRoww["Verifier"];
						$jj = $subCatRoww["Approver"];
						$msgbox = $subCatRoww["msgbox"];

						$hhExplode = explode(":", $hh);
						$GeoCoordinate = $hhExplode[0];
						if($GeoCoordinate == ""){
							$GeoCoordinate = null;
						}
						$GeoFence = $hhExplode[1];
						if($GeoFence == ""){
							$GeoFence = null;
						}

						$iconExplode = explode(",", $gg);
						$categoryIcon = $iconExplode[0];
						$subCategoryIcon = $iconExplode[1];

						$colorsExplode = explode(":", $colors);
						$catColors = $colorsExplode[0];
						$catColorsExplode = explode(",", $catColors);
						$catBgColor = $catColorsExplode[0];
						$catFontColor = $catColorsExplode[1];

						$subCatColors = $colorsExplode[1];
						$subCatColorsExplode = explode(",", $subCatColors);
						$bgColor = $subCatColorsExplode[0];
						$fontColor = $subCatColorsExplode[1];

						$json2 = array();
						$json2 = array(
							'menuId' => $aa,
							'Caption' => $bb,
							'Icon' => $subCategoryIcon,
							'bgColor' => $bgColor,
							'fontColor' => $fontColor,
							'subCategoryList' => array(),
							'checkpointId' => $ee,
							'active' => $ff,
							'Editable' => "",
							'GeoCoordinate' => $GeoCoordinate,
							'GeoFence' => $GeoFence,
							'verifier' => $ii,
							'approver' => $jj,
							'msgbox' => $msgbox
						);
						array_push($resultSubCatArr,$json2);
					}
				}
				$json1 = array();
				$json1 = array('Caption' => $catArr[$i], 
					'Icon' => $categoryIcon, 'bgColor' => $catBgColor, 'fontColor' => $catFontColor, 
					'subCategoryList' => $resultSubCatArr);
				array_push($resultArr,$json1);

			}
			else if($levelType == "THIRD"){
				$subCatSqll = "SELECT `Sub` FROM `Menu` WHERE `MenuId` in ($menuIds) and `Cat` = '$catArr[$i]'";
				$subCatQueryy=mysqli_query($conn,$subCatSqll);

				$categoryIcon = "";
				$resultSubCatArr = array();
				$subCatArr = array();
				while($subCatRoww = mysqli_fetch_assoc($subCatQueryy)){
					$subb = $subCatRoww["Sub"];
					if(!in_array($subb, $subCatArr) && ($subb != null || $subb != '') ){
						array_push($subCatArr,$subb);

						$subCategoryIcon = "";
						$resultCapArr = array();
						$captionArr = array();
						$captionSql = "SELECT * FROM `Menu` WHERE `MenuId` in ($menuIds) and `Cat` = '$catArr[$i]' and `Sub` = '$subb'";

						$captionQuery=mysqli_query($conn,$captionSql);
						while($captionRow = mysqli_fetch_assoc($captionQuery)){
							$caption = $captionRow["Caption"];
							if(!in_array($caption, $captionArr) && ($caption != null || $caption != '')){
								array_push($captionArr,$caption);

								$aa = $captionRow["MenuId"];
								$bb = $captionRow["Caption"];
								$ee = $captionRow["CheckpointId"];
								$ff = $captionRow["Active"];
								$gg = $captionRow["Icons"];
								$colors = $captionRow["Colors"];
								$hh = $captionRow["GeoFence"];
								$ii = $captionRow["Verifier"];
								$jj = $captionRow["Approver"];
								$msgbox = $captionRow["msgbox"];

								$hhExplode = explode(":", $hh);
								$GeoCoordinate = $hhExplode[0];
								if($GeoCoordinate == ""){
									$GeoCoordinate = null;
								}
								$GeoFence = $hhExplode[1];
								if($GeoFence == ""){
									$GeoFence = null;
								}

								$iconExplode = explode(",", $gg);
								$categoryIcon = $iconExplode[0];
								$subCategoryIcon = $iconExplode[1];

								$colorsExplode = explode(":", $colors);

								$catColors = $colorsExplode[0];
								$catColorsExplode = explode(",", $catColors);
								$catBgColor = $catColorsExplode[0];
								$catFontColor = $catColorsExplode[1];


								$subCatColors = $colorsExplode[1];
								$subCatColorsExplode = explode(",", $subCatColors);
								$subCatBgColor = $subCatColorsExplode[0];
								$subCatFontColor = $subCatColorsExplode[1];


								$captionColors = $colorsExplode[2];
								$captionColorsExplode = explode(",", $captionColors);
								$bgColor = $captionColorsExplode[0];
								$fontColor = $captionColorsExplode[1];

								$json3 = array();
								$json3 = array(
									'menuId' => $aa,
									'Caption' => $bb,
									'Icon' => $iconExplode[2],
									'bgColor' => $bgColor,
									'fontColor' => $fontColor,
									'checkpointId' => $ee,
									'active' => $ff,
									'Editable' => "",
									'GeoCoordinate' => $GeoCoordinate,
									'GeoFence' => $GeoFence,
									'verifier' => $ii,
									'approver' => $jj,
									'msgbox' => $msgbox
								);
								array_push($resultCapArr,$json3);
							}
						}
						$json2 = array();
						$json2 = array('Caption' => $subb, 
							'Icon' => $subCategoryIcon, 
							'bgColor'=> $subCatBgColor, 'fontColor' => $subCatFontColor, 
							'subCategoryList' => $resultCapArr);
						array_push($resultSubCatArr,$json2);
					}	
				}
				$json1 = array();
				$json1 = array('Caption' => $catArr[$i], 
					'Icon' => $categoryIcon, 
					'bgColor' => $catBgColor, 'fontColor' => $catFontColor, 
					'subCategoryList' => $resultSubCatArr);
				array_push($resultArr,$json1);
			}
		}

		for($j = 0; $j < count($zeroArr); $j++){
			
			$levelType = "ZERO";
			
			if($levelType == "ZERO"){
				$zeroSqlll = "SELECT * FROM `Menu` WHERE `MenuId` in ($zeroArr[$j])";
				$zeroQueryyy=mysqli_query($conn,$zeroSqlll);
				while($zeroRowww = mysqli_fetch_assoc($zeroQueryyy)){
					$aa = $zeroArr[$j];
					$bb = $zeroRowww["Cat"];
					$ee = $zeroRowww["CheckpointId"];
					$ff = $zeroRowww["Active"];
					$gg = $zeroRowww["Icons"];
					$colors = $zeroRowww["Colors"];
					$hh = $zeroRowww["GeoFence"];
					$hhExplode = explode(":", $hh);
					$GeoCoordinate = $hhExplode[0];
					if($GeoCoordinate == ""){
						$GeoCoordinate = null;
					}
					$GeoFence = $hhExplode[1];
					if($GeoFence == ""){
						$GeoFence = null;
					}


					$iconExplode = explode(",", $gg);
					$categoryIcon = $iconExplode[0];

					$colorsExplode = explode(":", $colors);
					$catColors = $colorsExplode[0];
					$catColorsExplode = explode(",", $catColors);
					$bgColor = $catColorsExplode[0];
					$fontColor = $catColorsExplode[1];

					$json1 = array();
					$json1 = array(
						'menuId' => $aa,
						'Caption' => "",
						'Icon' => $categoryIcon, 
						'bgColor' => $bgColor,
						'fontColor' => $fontColor,
						'subCategoryList' => array(),
						'checkpointId' => $ee,
						'active' => $ff,
						'Editable' => "",
						'GeoCoordinate' => $GeoCoordinate,
						'GeoFence' => $$GeoFence
						
					);
					array_push($resultArr,$json1);
				}
			}

		}

		$confSql = "Select * from configuration";
		$confQuery = mysqli_query($conn, $confSql);
		$conf = mysqli_fetch_assoc($confQuery);
		$confObj = new StdClass;
		$confObj -> inf = $conf['inf'];
		$confObj -> conn = $conf['conf'];
		$confObj -> Start = $conf['start'];
		$confObj -> End = $conf['end'];
		$confObj -> Battery = $conf['Battery'];
		$confObj -> Image = $conf['image'];
		$res = new StdClass;
		if($menuIds != '34') $res -> tabName = 'Library';
		else $res -> tabName = 'Maps';
		$res->menu = $resultArr;
		return $res;
	}
}
?>