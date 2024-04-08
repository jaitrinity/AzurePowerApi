<?php
include("dbConfiguration.php");
$empId=$_REQUEST['empId'];
$roleId=$_REQUEST['roleId'];

$menuArr = array();


if($roleId != null){
	$getTabSql= "SELECT * from `RoleMaster` where `RoleId` = $roleId";
}
$getTabResult = mysqli_query($conn,$getTabSql);
if(mysqli_num_rows($getTabResult) > 0){
	$tr = mysqli_fetch_Array($getTabResult);
	$mapMenuIds = $tr['MenuId'];
	$tabId = $tr['TabId'];

	if($tabId != null || $tabId != ""){
		$tabSql = "SELECT * from `Tab` where `Id` in ($tabId) and `Active` = 1 order by `Seq`";
		$tabQuery=mysqli_query($conn,$tabSql);
		$hashMap = new StdClass;
		
		//$jsonResArray = array();
		while($tabRow = mysqli_fetch_assoc($tabQuery)){
			if($tabRow['TabName'] == "Pending"){
				//echo $tabRow['TabName']."\n";

				require_once 'NewAssign1.php';
				$classObj = new NewAssign1;
   	 			$result1 = $classObj->getNewAssign1Data($empId, $roleId);
   	 			$hashMap->{$tabRow["Id"]} = $result1;
				
			}
			else if($tabRow['TabName'] == "Completed"){
				//echo $tabRow['TabName']."\n";

				require_once 'SubmittedClass.php';
				$classObj = new SubmittedClass;
   	 			$result1 = $classObj->submitted($empId, $roleId);
   	 			$hashMap->{$tabRow["Id"]} = $result1;
			}
			else if($tabRow['TabName'] == "All"){
				$jsonResArray = array();
				$jsonRes = "";

				require_once 'NewAssign1.php';
				$classObj = new NewAssign1;
   	 			$res1 = $classObj->getNewAssign1Data($empId, $roleId);


   	 			require_once 'SubmittedClass.php';
				$classObj = new SubmittedClass;
   	 			$res2 = $classObj->submitted($empId, $roleId);
		
				$menuIds = $mapMenuIds;
				
				require_once 'TabMenuClass.php';
				$classObj = new TabMenuClass;
   	 			$res3 = $classObj->getTabMenu($menuIds);

				$all = new StdClass;
				$all->Pending = $res1;
				$all->Completed = $res2;
				$all->Library  = $res3;
				$hashMap->{$tabRow["Id"]} = $all;
			}
			else if($tabRow['TabName'] == "Maps"){
				$menuIds = "85";

				require_once 'TabMenuClass.php';
				$classObj = new TabMenuClass;
   	 			$result1 = $classObj->getTabMenu($menuIds);
   	 			$hashMap->{$tabRow["Id"]} = $result1;
			}		
			
			else{
				$menuIds = $mapMenuIds;

				require_once 'TabMenuClass.php';
				$classObj = new TabMenuClass;
   	 			$result1 = $classObj->getTabMenu($menuIds);
   	 			$hashMap->{$tabRow["Id"]} = $result1;
			}		
		}

		echo json_encode($hashMap);
	}
}

?>