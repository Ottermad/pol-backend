<?php

ini_set("display_errors", 1); 
error_reporting(E_ALL ^ E_STRICT ^ E_NOTICE);
header("Content-Type: text/json");

try{
	$db = new PDO("mysql:host=localhost;dbname=sufigaff_pol","********","*********");
}catch(PDOException $e){
	echo "A database error occured: ".$e->getMessage();
	die();
}

$action = $_GET["action"];
$query = $_GET["query"];

if($query === "user" && $action === "exists"){
	if(isset($_GET["android_id"])){
		$userCheck = $db->prepare("SELECT COUNT(*) FROM users WHERE android_id = :android_id");
		$userCheck->bindParam(":android_id", $_GET["android_id"]);
		$userCheck->execute();
		$userExists = $userCheck->fetchColumn() != 0;

		$arr = [
			"exists" => (bool)$userExists
		];
		echo json_encode($arr,JSON_UNESCAPED_SLASHES);
	}
}

if($query === "candidates"){
	if(isset($_GET["constituency"])){
		$obj = json_decode(file_get_contents("http://mapit.mysociety.org/areas/".$_GET["constituency"]));
		foreach($obj as $key => $val){
			if($val->name === $_GET["constituency"]){
				$id = $val->id;
				break;
			}
		}

		$memberships = json_decode(file_get_contents("http://yournextmp.popit.mysociety.org/api/v0.1/posts/".$id."?embed=membership.person"))->result->memberships;
		$arr = [];
		$parties = [];
		foreach($memberships as $key => $val){
			$person = $val->person_id;
			if(!in_array($person->party_memberships->{"2015"}->name, $parties)){
				array_push($parties, $person->party_memberships->{"2015"}->name);
				if(isset($person->party_memberships->{"2015"})){
					array_push($arr,[
						"name" => $person->name,
						"gender" => strtolower($person->gender),
						"email" => $person->email,
						"party" => $person->party_memberships->{"2015"}->name
					]);
				}
			}
		}

		echo json_encode($arr,JSON_UNESCAPED_SLASHES);
	}
}

if($query === "user" && $action === "new"){
	if(isset($_GET["android_id"], $_GET["lat"], $_GET["long"])){
		if(!isset($_GET["admin"])){
			$_GET["admin"] = 0;
		}

		$checkUser = $db->prepare("SELECT COUNT(id) FROM users WHERE android_id = :android_id");
		$checkUser->bindParam(":android_id",$_GET["android_id"]);
		$checkUser->execute();
		$userCount = $checkUser->fetchColumn();

		if($userCount == 0){
			$obj = json_decode(file_get_contents("http://mapit.mysociety.org/point/4326/".$_GET["long"].",".$_GET["lat"]."?type=WMC"));
			foreach($obj as $key => $val){
				$data = $obj->{$key};
				break;
			}
			$const = $data->name;

			$signupTime = time();
			$insertUser = $db->prepare("INSERT INTO users(android_id,signup_time,admin,constituency) VALUES(:android_id,$signupTime,:admin,:constituency)");
			$insertUser->bindParam(":android_id",$_GET["android_id"]);
			$insertUser->bindParam(":admin",$_GET["admin"]);
			$insertUser->bindParam(":constituency",$const);
			$insertUser->execute();
		}

		$getUser = $db->prepare("SELECT * FROM users WHERE android_id = :android_id");
		$getUser->bindParam(":android_id",$_GET["android_id"]);
		$getUser->execute();
		$user = $getUser->fetch(PDO::FETCH_ASSOC);
		$user["admin"] = (bool)$user["admin"];

		echo json_encode($user,JSON_UNESCAPED_SLASHES);
	}
}

if($query === "post" && $action === "new"){
	if(isset($_GET["content"],$_GET["tags"],$_GET["android_id"])){
		if(isset($_GET["parent"]) && $_GET["parent"] != 0){
			$getParent = $db->prepare("SELECT title FROM posts WHERE id = :parent");
			$getParent->bindParam(":parent",$_GET["parent"]);
			$getParent->execute();
			$_GET["title"] = $getParent->fetchColumn();
		}else{
			$_GET["parent"] = 0;
		}

		$parent = $_GET["parent"];

		$getConst = $db->prepare("SELECT constituency FROM users WHERE android_id = :android_id");
		$getConst->bindParam(":android_id",$_GET["android_id"]);
		$getConst->execute();
		$const = $getConst->fetchColumn();

		$postTime = time();
		$insertPost = $db->prepare("INSERT INTO posts(content,title,parent,timestamp,tags,creator,constituency) VALUES(:content,:title,:parent,$postTime,:tags,:android_id,:constituency)");
		$insertPost->bindParam(":title",$_GET["title"]);
		$insertPost->bindParam(":content",$_GET["content"]);
		$insertPost->bindParam(":parent",$parent);
		$insertPost->bindParam(":tags",$_GET["tags"]);
		$insertPost->bindParam(":android_id",$_GET["android_id"]);
		$insertPost->bindParam(":constituency",$const);
		$insertPost->execute();

		echo "{\"success\":true}";
	}
}

if($query === "user" && $action === "return"){
	if(isset($_GET["android_id"])){
		$getUser = $db->prepare("SELECT * FROM users WHERE android_id = :android_id");
		$getUser->bindParam(":android_id",$_GET["android_id"]);
		$getUser->execute();
		$user = $getUser->fetch(PDO::FETCH_ASSOC);

		echo json_encode($user,JSON_UNESCAPED_SLASHES);
	}
}

if($query === "post" && $action === "upvote"){
	if(isset($_GET["android_id"], $_GET["post_id"])){
		$getUpvotes = $db->prepare("SELECT upvotes FROM posts WHERE post_id = :post_id");
		$getUpvotes->bindParam(":post_id",$_GET["post_id"]);
		$getUpvotes->execute();
		$upvotes = $getUpvotes->fetchColumn();
		$newUpvotes = $upvotes.",".$_GET["android_id"];

		$insertUpvotes = $db->prepare("UPDATE posts WHERE post_id = :post_id SET upvotes = :upvotes");
		$insertUpvotes->bindParam(":post_id",$_GET["post_id"]);
		$insertUpvotes->bindParam(":upvotes",$newUpvotes);
		$insertUpvotes->execute();

		$newNumber = count(split(",",$newUpVotes));

		echo "{\"success\":true,\"upvoteTotal\":".$newNumber."}";
	}
}

if($query === "post" && $action === "return"){

	$limit = 10;
	$order = "timestamp";

	if(isset($_GET["limit"])){
		$limit = $_GET["limit"];
	}

	if(isset($_GET["order"])){
		$order = $_GET["order"];
	}

	if(isset($_GET["android_id"])){
		$getConst = $db->prepare("SELECT constituency FROM users WHERE android_id = :android_id");
		$getConst->bindParam(":android_id",$_GET["android_id"]);
		$getConst->execute();
		$const = $getConst->fetchColumn();

		$getPosts = $db->prepare("SELECT * FROM posts WHERE creator = :android_id AND constituency = :constituency ORDER BY $order DESC LIMIT $limit");
		$getPosts->bindParam(":android_id",$_GET["android_id"]);
		$getPosts->bindParam(":constituency",$const);
		$getPosts->execute();
		$posts = $getPosts->fetchAll(PDO::FETCH_ASSOC);
	}else if(isset($_GET["post_id"])){
		$getPost = $db->prepare("SELECT * FROM posts WHERE id = :post_id");
		$getPost->bindParam(":post_id",$_GET["post_id"]);
		$getPost->execute();
		$posts = $getPost->fetch(PDO::FETCH_ASSOC);
		$posts["numberUpvotes"] = count(split(",",$posts["upvotes"]));
	}else if(isset($_GET["parent"]) && $_GET["parent"] === "0"){
		$getPosts = $db->prepare("SELECT * FROM posts WHERE constituency = :constituency AND parent = :parent ORDER BY $order DESC LIMIT $limit");
		$getPosts->bindParam(":parent",$_GET["parent"]);
		$getPosts->bindParam(":constituency",$_GET["constituency"]);
		$getPosts->execute();
		$posts = $getPosts->fetchAll(PDO::FETCH_ASSOC);
	}else if(isset($_GET["parent"])){
		$getPosts = $db->prepare("SELECT * FROM posts WHERE parent = :parent ORDER BY $order DESC LIMIT $limit");
		$getPosts->bindParam(":parent",$_GET["parent"]);
		$getPosts->execute();
		$posts = $getPosts->fetchAll(PDO::FETCH_ASSOC);
	}else if(isset($_GET["tag"])){
		$getPosts = $db->prepare("SELECT * FROM posts WHERE tags LIKE :tag AND constituency = :constituency ORDER BY $order DESC LIMIT $limit");
		$getPosts->bindParam(":constituency",$_GET["constituency"]);
		$getPosts->bindParam(":tag",$_GET["tag"]);
		$getPosts->execute();
		$posts = $getPosts->fetchAll(PDO::FETCH_ASSOC);
	}else{
		$getPosts = $db->prepare("SELECT * FROM posts WHERE constituency = :constituency ORDER BY $order DESC LIMIT $limit");
		$getPosts->bindParam(":constituency",$_GET["constituency"]);
		$getPosts->execute();
		$posts = $getPosts->fetchAll(PDO::FETCH_ASSOC);
	}

	echo json_encode($posts,JSON_NUMERIC_CHECK,JSON_UNESCAPED_SLASHES);
}

$db = null;

?>
