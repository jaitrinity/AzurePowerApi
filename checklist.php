<?php
include("dbConfiguration.php");
$methodType = $_SERVER['REQUEST_METHOD'];
if($methodType != "POST"){
	return;
}
$json = file_get_contents('php://input');
$jsonData=json_decode($json);

$empId=$jsonData->empId;
$roleId=$jsonData->roleId;


$menuArr = array();
$roleSql = "SELECT distinct `MenuId` FROM `RoleMaster` WHERE `RoleId` = '$roleId' ";
$roleQuery=mysqli_query($conn,$roleSql);
while($roleRow = mysqli_fetch_assoc($roleQuery)){
	$roleMenuId = $roleRow['MenuId'];
	$roleMenuIdExplode = explode(",",$roleMenuId);
	for($i=0;$i<count($roleMenuIdExplode);$i++){
		array_push($menuArr,$roleMenuIdExplode[$i]);
	}
}

$menuIds= implode(",", $menuArr);
$menuSql = "SELECT `Category`,`MenuId` FROM `Menu` WHERE `MenuId` in ($menuIds)";
$menuQuery=mysqli_query($conn,$menuSql);
$catArr = array();
while($menuRow = mysqli_fetch_assoc($menuQuery)){
	$cat = $menuRow["Category"];
	if(!in_array($cat, $catArr) && ($cat != null || $cat != '')){
		array_push($catArr,$cat);
	}
}

$resultArr = array();
for($i = 0; $i < count($catArr); $i++){
	$subCatSql = "SELECT `SubCategory`,`Caption` FROM `Menu` WHERE `MenuId` in ($menuIds) and `Category` = '$catArr[$i]'";
	// echo $subCatSql;
	$subCatQuery=mysqli_query($conn,$subCatSql);
	$levelType = "";
	while($subCatRow = mysqli_fetch_assoc($subCatQuery)){
		$sub = $subCatRow["SubCategory"];
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
		$subCatSqlll = "SELECT * FROM `Menu` WHERE `MenuId` in ($menuIds) and `Category` = '$catArr[$i]'";
		// echo $subCatSqlll.'--';
		$subCatQueryyy=mysqli_query($conn,$subCatSqlll);
		while($subCatRowww = mysqli_fetch_assoc($subCatQueryyy)){
			$aa = $subCatRowww["MenuId"];
			$bb = $subCatRowww["Category"];
			$ee = $subCatRowww["CheckpointId"];
			$gg = $subCatRowww["Icons"];
			$colors = $subCatRowww["Colors"];
			$ii = $subCatRowww["VerifierChkId"];
			$jj = $subCatRowww["ApproverChkId"];


			$iconExplode = explode(",", $gg);
			$categoryIcon = $iconExplode[0];

			$colorsExplode = explode(":", $colors);
			$catColors = $colorsExplode[0];
			$catColorsExplode = explode(",", $catColors);
			$bgColor = $catColorsExplode[0];
			$fontColor = $catColorsExplode[1];

			$json1 = array(
				'menuId' => $aa,
				'caption' => $catArr[$i],
				'icon' => $categoryIcon, 
				'bgColor' => $bgColor,
				'fontColor' => $fontColor,
				'subCategoryList' => array(),
				'checkpointId' => $ee,
				// 'verifier' => $ii,
				// 'approver' => $jj
			);
			array_push($resultArr,$json1);
		}
	}
	else if($levelType == "SECOND"){
		$subCatSqll = "SELECT * FROM `Menu` WHERE `MenuId` in ($menuIds) and `Category` = '$catArr[$i]'";
		$subCatQueryy=mysqli_query($conn,$subCatSqll);
		$categoryIcon = "";
		$resultSubCatArr = array();
		$subCatArr = array();
		while($subCatRoww = mysqli_fetch_assoc($subCatQueryy)){
			$subb = $subCatRoww["SubCategory"];
			if(!in_array($subb, $subCatArr) && ($subb != null || $subb != '') ){
				$aa = $subCatRoww["MenuId"];
				$bb = $subCatRoww["SubCategory"];
				$ee = $subCatRoww["CheckpointId"];
				$gg = $subCatRoww["Icons"];
				$colors = $subCatRoww["Colors"];
				$ii = $subCatRoww["VerifierChkId"];
				$jj = $subCatRoww["ApproverChkId"];

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

				$json2 = array(
					'menuId' => $aa,
					'caption' => $bb,
					'icon' => $subCategoryIcon,
					'bgColor' => $bgColor,
					'fontColor' => $fontColor,
					'captionList' => array(),
					'checkpointId' => $ee,
					// 'verifier' => $ii,
					// 'approver' => $jj
				);
				array_push($resultSubCatArr,$json2);
			}
		}
		$json1 = array('Caption' => $catArr[$i], 
			'Icon' => $categoryIcon, 'bgColor' => $catBgColor, 'fontColor' => $catFontColor, 
			'subCategoryList' => $resultSubCatArr);
		array_push($resultArr,$json1);

	}
	else if($levelType == "THIRD"){
		$subCatSqll = "SELECT `SubCategory` FROM `Menu` WHERE `MenuId` in ($menuIds) and `Category` = '$catArr[$i]'";
		$subCatQueryy=mysqli_query($conn,$subCatSqll);

		$categoryIcon = "";
		$resultSubCatArr = array();
		$subCatArr = array();
		while($subCatRoww = mysqli_fetch_assoc($subCatQueryy)){
			$subb = $subCatRoww["SubCategory"];
			if(!in_array($subb, $subCatArr) && ($subb != null || $subb != '') ){
				array_push($subCatArr,$subb);

				$subCategoryIcon = "";
				$resultCapArr = array();
				$captionArr = array();
				$captionSql = "SELECT * FROM `Menu` WHERE `MenuId` in ($menuIds) and `Category` = '$catArr[$i]' and `SubCategory` = '$subb'";

				$captionQuery=mysqli_query($conn,$captionSql);
				while($captionRow = mysqli_fetch_assoc($captionQuery)){
					$caption = $captionRow["Caption"];
					if(!in_array($caption, $captionArr) && ($caption != null || $caption != '')){
						array_push($captionArr,$caption);

						$aa = $captionRow["MenuId"];
						$bb = $captionRow["Caption"];
						$ee = $captionRow["CheckpointId"];
						$gg = $captionRow["Icons"];
						$colors = $captionRow["Colors"];
						$ii = $captionRow["VerifierChkId"];
						$jj = $captionRow["ApproverChkId"];

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

						$json3 = array(
							'menuId' => $aa,
							'caption' => $bb,
							'icon' => $iconExplode[2],
							'bgColor' => $bgColor,
							'fontColor' => $fontColor,
							'checkpointId' => $ee,
							// 'verifier' => $ii,
							// 'approver' => $jj,
						);
						array_push($resultCapArr,$json3);
					}
				}
				$json2 = array('Caption' => $subb, 
					'Icon' => $subCategoryIcon, 
					'bgColor'=> $subCatBgColor, 'fontColor' => $subCatFontColor, 
					'captionList' => $resultCapArr);
				array_push($resultSubCatArr,$json2);
			}	
		}
		$json1 = array('caption' => $catArr[$i], 
			'icon' => $categoryIcon, 
			'bgColor' => $catBgColor, 'fontColor' => $catFontColor, 
			'subCategoryList' => $resultSubCatArr);
		array_push($resultArr,$json1);
	}
}
// echo json_encode($resultArr);
$code = 0;
if(count($resultArr) !=0){
	$code=200;
}
else{
	$code=403;
}
$output=array(
	'code'=>$code,
	'checklist'=>$resultArr
);
echo json_encode($output);

?>