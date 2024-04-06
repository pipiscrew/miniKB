<?php

@session_start();

require_once ('general.php');

if (!isset($_POST['action'])){
	ReportJS(0, 'No valid parameter!');
	exit;
}

$action = $_POST['action'];

if (!isset($_SESSION["id"])) {
	$allowedActions = ['GetNodes', 'GetSearchNodes', 'GetArticle'];
	
	if (!in_array($action, $allowedActions)) {
		ReportJS(0, 'User is not logged in');
		exit;
	}
}

switch ($action) {
	case 'GetNodes' : //get records
		GetNodes();
		break;
	case 'GetSearchNodes': //search records
		GetSearchNodes($_POST['s']);
		break;
	case 'GetArticle' : //get record detail
		GetArticle($_POST['id']);
		break;
	case 'SaveArticle':  //save record
		$parentID = isset($_POST['parentID']) ? $_POST['parentID'] : 0;
		$id = isset($_POST['id']) ? $_POST['id'] : 0;

		SaveArticle($id, $parentID, $_POST['articleName'], $_POST['content']);
		break;
	case 'DeleteArticle' : //delete record
		$id = isset($_POST['id']) ? $_POST['id'] : -1;
		DeleteArticle($id);
		break;
	case 'MoveSearch' : // move - search parents
		MoveSearch($_POST['s']);
		break;
	case 'MoveSave' : // move - save to parent
		$parentID = isset($_POST['parentID']) ? $_POST['parentID'] : 0;
		$id = isset($_POST['id']) ? $_POST['id'] : 0;

		MoveSave($id, $parentID);
		break;
	default :
		ReportJS(0, 'No action defined!');
		exit;
}


function SaveArticle($id, $parentID, $nodeName, $nodeCode){
    $db = new dbase();
    $db->connect_sqlite();

	if ( isset($id) && $id > 0 ) //update
	{
		$sql = "UPDATE codes SET NodeName = ?, NodeCode= ? where nodeid= ?";
		if (!$db->executeSQL($sql, array($nodeName, $nodeCode, $id))) {
			ReportJS(0, 'Error record is not updated!');
		} else {
			//ReportJS(1, 'success updated');
			ReportJSarray( array('code'=> 1, 'id'=> $id) );
		}
	}
	else { //insert
		$sql = "INSERT INTO codes(ParentNode,IsFolder,NodeName,NodeCode) VALUES(?,?,?,?)";
		if (!$db->executeSQL($sql, array($parentID, 1, $nodeName, $nodeCode))) {
			ReportJS(0, 'Error record is not added!');
		} else {

			if ($parentID>0) {//aka if not ROOT 
				//update parent that is FOLDER now! (this needed for articles order by), the logic is in reverse 0 means isFolder
				$sql = "UPDATE codes SET IsFolder = 0 where nodeid= ?";
				if (!$db->executeSQL($sql, array($parentID))) {
					ReportJS(0, 'Error record added but parent is not updated that isFolder!');
				} else {
					// ReportJS(1, 'success added');
					ReportJSarray( array('code'=> 1, 'id'=> $db->getConnection()->lastInsertId()) );
				}
			}
			else {
				ReportJSarray( array('code'=> 1, 'id'=> $db->getConnection()->lastInsertId()) );
			}
		}
	}
}

function GetArticle($id){
    $db = new dbase();
    $db->connect_sqlite();
    
    $r = $db->getRow("select nodeid,nodename,nodecode,parentnode,isfolder from codes where nodeid=?",array($id));

	/////////////show path logic [start]
	$pathArr;
	$nodeId = $r['ParentNode'];
	if ($r['ParentNode']!='0') {
		
		while (true) {
			$p = $db->getRow("select nodeid,nodename,nodecode,parentnode,isfolder from codes where nodeid=?",array($nodeId));
			$pathArr[] = $p['NodeName'];

			if ($p['ParentNode'] == '0') {
				break;
			}

			$nodeId = $p['ParentNode'];
		}
	}

	if (!isset($pathArr))
		$pathArr[]='';
	/////////////show path logic [end]

	header("Content-Type: application/json", true);
    echo json_encode(array('NodeName' => $r['NodeName'], 'NodeCode' => $r['NodeCode'], 'NodePath' => implode(" > ", array_reverse($pathArr))));    
}

function DeleteArticle($id){
    $db = new dbase();
    $db->connect_sqlite();

	if ($db->getScalar("select count(1) from codes where parentnode=?", array($id)) > 0)
		ReportJS(2, 'found related record(s), cannot be deleted');
	else {
		$sql = "delete from codes where nodeid= ?";
		if (!$db->executeSQL($sql, array($id))) {
			ReportJS(0, 'Error - record is not deleted!');
		} else { //todo: if parentnode does not have children anymore, should turn the isfolder=1
			ReportJS(1, 'success delete');
		}
	}
}


//===========================================
//		move search + save
//===========================================
function MoveSearch($str){
    $db = new dbase();
    $db->connect_sqlite();

	$sql = "select c.nodeid, COALESCE(c2.nodename, '') || ' > ' || c.nodename as NodeName from codes c
	left join codes c2 on c2.NodeID = c.ParentNode
	where c.nodename like '%' || ? || '%'
	order by NodeName COLLATE NOCASE ASC";

	$r = $db->getSet($sql, array($str));

	header("Content-Type: application/json", true);
	echo json_encode($r);
}

function MoveSave($id, $parentID){
    $db = new dbase();
    $db->connect_sqlite();

	$sql = "UPDATE codes SET parentnode=? where nodeid= ?";
	if ($db->executeSQL($sql, array($parentID, $id))) {

		if ($parentID > 0) { //turn also the parent isFolder = 0
			$sql = "UPDATE codes SET isfolder=0 where nodeid= ?"; 

			$db->executeSQL($sql, array($parentID));
		}
	
		ReportJSarray( array('code'=> 1, 'id'=> $id) );
	} else {
		ReportJS(0, 'Error record is not updated!');
	}
}

//===========================================
//		get records + search records
//===========================================
function GetNodes() {
	$db = new dbase();
	$db->connect_sqlite();

	$heads = $db->getSet("select nodeid,nodename,parentnode,isfolder from codes where parentnode=0 order by isfolder,nodename COLLATE NOCASE ASC",null);

	$arr = null;

	foreach($heads as $row) {
		$arr[] = array("text" => $row['NodeName'],"href" => $row['NodeID'], "nodes" => GetChildren($db, $row['NodeID']) );
	}

	header("Content-Type: application/json", true);

	echo json_encode($arr);
}

//used only by GetNodes
function GetChildren($db, $nodeID) {
	$children = $db->getSet("select nodeid,nodename,parentnode,isfolder from codes where parentnode=? order by isfolder,nodename COLLATE NOCASE ASC",array($nodeID));

	$arrChildren = null;
	
	foreach($children as $child) {	
			$arrChildren[] = array("text" => $child['NodeName'],"href" => $child['NodeID'], "nodes" => GetChildren($db, $child['NodeID']) );
	}
	
	return $arrChildren;
}

function GetSearchNodes($s){
	$db = new dbase();
	$db->connect_sqlite();
		
	$r = $db->getSet("select nodeid,nodename,parentnode,isfolder from codes where nodename like '%' || ? || '%' or nodecode like '%' || ? || '%' order by nodename COLLATE NOCASE ASC",array($s,$s));

	$arr = null;
	foreach($r as $row) {
		$arr[] = array("text" => $row['NodeName'],"href" => $row['NodeID'], "nodes" => null );
	}
	
	header("Content-Type: application/json", true);
	
	echo json_encode($arr);
}
//===========================================
