<?php

session_start();

date_default_timezone_set('Asia/Kolkata');
$DATETIME= date('Y-m-d h:i:s', time());

# --- DB INFO ----

$DB_SERVERNAME = "localhost";
$DB_USERNAME = "id15249132_admin";
$DB_DBNAME = "id15249132_relationdb";
$DB_PASSWORD = "Password123$";

# -----------------

$srv = [
    "login"=>"login",
    "logout" =>"logout",
    "post" => "post",
    "comment" => "comment",
    "like" => "like",
    "joingroup" => "joingroup",
    "leavegroup" => "leavegroup",
    "addfriend" => "addfriend",
    "removefriend" => "removefriend",
    "creategroup" => "creategroup",
    "register" => "register",
    "deregister" => "deregister",
    "requestpage" => "requestpage"
    ];

$msg = [
    "sregister" => "[OK] Registration successful",
    "fregister" => "[FAIL] Unsuccessful registration",
  	"nlogin" => "[FAIL] Not logged in",
    "slogin" => "[OK] Login successful",
    "flogin" => "[FAIL] Unsuccessful login",
    "logout" => "[OK] Logout successful",
    "errint" => "[FAIL] Internal Error",
    "addfrnd" => "[OK] Friend Added",
    "erraddfrnd" => "[FAIL] Friend couldn't be added",
  	"scom" => "[OK] Comment Successful",
  	"fcom" => "[FAIL] Comment Failed",
  	"slike" => "[OK] Like Successful",
  	"flike" => "[FAIL] Like Failed",
  	"fpost" => "[FAIL] Failed to post",
  	"spost" => "[OK] Post successful"
    ];

function secureGET(){
    foreach($_GET as $k => $v){
        $_GET[$k] = htmlentities($v);
    }
}

function connectDB()
{
    global $DB,$DB_SERVERNAME,$DB_DBNAME,$DB_USERNAME,$DB_PASSWORD,$msg;
    try{
        $DB = new PDO("mysql:host=$DB_SERVERNAME;dbname=$DB_DBNAME",$DB_USERNAME,$DB_PASSWORD);
        $DB->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }
    catch(PDOException $e){
        die($msg["errint"]);
    }
}

function authenticateLogin($user,$pwd){
    global $DB;
    $query = "select * from Users where username='$user' and passwd='$pwd'";
    try{
    $store = $DB->query($query);
    if($store->rowCount()>0){
        $_SESSION['user'] = $store->fetchAll()[0];
        return true;
    }}catch(PDOException $e){}
    return false;
}

function login(){
    global $msg,$srv;
    if(!isset($_SESSION['user'])){
        if(isset($_GET["service"])){
            if($_GET["service"] == $srv["login"]){
                if(isset($_GET["usr"]) and isset($_GET["pwd"])){
                    if(authenticateLogin($_GET["usr"],$_GET["pwd"]))
                        die($msg["slogin"]);
                    else
                        die($msg["flogin"]);
                }
                else
                    die($msg["flogin"]);
            }
            else die($msg["nlogin"]);
        }
        else die($msg["nlogin"]);
    }
}

function logout(){
    global $msg;
    if(isset($_SESSION['user'])) unset($_SESSION['user']);
    die($msg["logout"]);
}


function addfriend(){
    global $msg,$DB;
    if(isset($_GET["fid"])){
        $user_id = $_SESSION["user"]["user_id"];
        $user_id_f = $_GET["fid"];
        $query = "insert into Friend values ($user_id,$user_id_f);";
        try{
            $DB->query($query);
            die($msg["addfrnd"]);
        }
        catch(PDOException $e){
        }
    }
    die($msg["erraddfrnd"]);
}


function post(){
    global $msg,$DB,$DATETIME;
  	if(isset($_GET["pageid"]) and isset($_GET["data"]) and isset($_GET["title"])){
      $pageid = $_GET["pageid"];
      $data = $_GET["data"];
      $title = $_GET["title"];
      $user_id = $_SESSION["user"]["user_id"];
      try{
        $query = "select max(post_id) as postid from UserPost";
        $postid = $DB->query($query)->fetchAll()[0]["postid"];
        if($postid == null) $postid = 0;
        else $postid += 1;
      	$query = "insert into UserPost values ($user_id,$postid,'$title','$DATETIME');";
        $DB->query($query);
        $query = "select max(data_id) as dataid from PageData";
        $dataid = $DB->query($query)->fetchAll()[0]["dataid"];
        if($dataid == null) $dataid = 0;
        else $dataid += 1;
        $query = "insert into PageData values ($pageid,$dataid,'$data');";
        $DB->query($query);
        $query = "insert into PostData values ($postid,$dataid);";
        $DB->query($query);
        $query = "insert into PostPage values ($postid,$pageid);";
        $DB->query($query);
        die($msg["spost"]);
      }
      catch(PDOException $e){echo $e->getMessage();}
  	}
     die($msg["fpost"]);
}
  
           
function comment(){
    global $msg,$DB,$DATETIME;
  	if(isset($_GET["postid"]) and isset($_GET["text"])){
      try{
  		$query = "select max(comment_id) as id from UserComment";
      	$id = $DB->query($query)->fetchAll()[0]["id"];
        if($id == null) $id = 0;
        else $id += 1;
        $user_id = $_SESSION(["user"]["user_id"]);
        $query = "insert into UserComment values ($user_id,$id,'$DATETIME');";
        $DB->query($query);
        $text = $_GET["text"];
        $post = $_GET["postid"];
        $query = "insert into CommentPost values ($id,$post,$text);";
        $DB->query($query);
        die($msg["scom"]);
    }
    catch(PDOException $e){}
    }
  	die($msg["fcom"]);
}

           
function like(){
    global $msg,$DB;
    if(isset($_GET["type"]))
    {
      	if(isset($_GET["postid"]) and $_GET["type"] == "post"){
          try{
            $query = "select max(like_id) as id from UserLike";
            $id = $DB->query($query)->fetchAll()[0]["id"]+1;
            $user_id = $_SESSION(["user"]["user_id"]);
            $query = "insert into UserLike values ($user_id,$id);";
            $DB->query($query);
            $post = $_GET["postid"];
            $query = "insert into PostLike values ($id,$post);";
            $DB->query($query);
            die($msg["slike"]);
          }
          catch(PDOException $e){}
        }
    
      else if($_GET["type"] == "comment" and isset($_GET["commentid"])){
          try{
            $query = "select max(like_id) as id from UserLike";
            $id = $DB->query($query)->fetchAll()[0]["id"]+1;
            $user_id = $_SESSION(["user"]["user_id"]);
            $query = "insert into UserLike values ($user_id,$id);";
            $DB->query($query);
            $post = $_GET["commentid"];
            $comment = $_GET["commentid"];
            $query = "insert into CommentLike values ($id,$comment);";
            $DB->query($query);
            die($msg["slike"]);
          }
          catch(PDOException $e){}
    	}
    }
  die($msg["flike"]);
}
  
  

function joingroup(){
    
}

  
function leavegroup(){
    
}

  
function creategroup(){
    
}

  
function removegroup(){
    
}

  
function register(){
  global $msg,$srv;
  if(!isset($_SESSION['user'])){
      if(isset($_GET["service"])){
          if($_GET["service"] == $srv["register"]){
              if(isset($_GET["usr"]) and isset($_GET["pwd"])){
                 
                	$query = "select max(user_id) as id from Users";
        					$id = $DB->query($query)->fetchAll()[0]["id"]+1;
                	$query = "insert into Users values ($id);";
                	$user_id = $_GET["usr"];
                	$query = "select max(page_id) as id from Profile";
        					$id = $DB->query($query)->fetchAll()[0]["id"]+1;
                	$query = "insert into values ($id,$user_id);";
                  die($msg["sregister"]);
              }
              else
                  die($msg["fregister"]);
          }
          else die($msg["fregister"]);
      }
      else die($msg["fregister"]);
  }

}

  
function deregister(){
    
}

  
function requestPage(){
  
}

  
secureGET();
connectDB();
login();

if(isset($_GET["service"])){
    switch($_GET["service"]){
        case $srv['logout']:
            logout();
            break;
        case $srv['login']:
            die('[OK] Already logged in');
            break;
        case $srv['post']:
            post();
            break;
        case $srv['comment']:
            comment();
            break;
        case $srv['like']:
            like();
            break;
        case $srv['joingroup']:
            joingroup();
            break;
        case $srv['leavegroup']:
            leavegroup();
            break;
        case $srv['addfriend']:
            addfriend();
            break;
        case $srv['removefriend']:
            removefriend();
            break;
        case $srv['creategroup']:
            creategroup();
            break;
        case $srv['removegroup']:
            removegroup();
            break;
        case $srv['register']:
            register();
            break;
        case $srv['deregister']:
            deregister();
            break;
        case $srv['requestpage']:
            requestpage();
            break;
        default:
            die('[FAIL] Unrecognized service requested');
    }
}
else die("No service availed");
?>