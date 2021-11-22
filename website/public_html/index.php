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
    "requestpage" => "requestpage",
    "removegroup" => "removegroup",
    "search" => "search"
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

function securePOST(){
    foreach($_POST as $k => $v){
        $_POST[$k] = htmlentities($v);
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
                if(isset($_POST["usr"]) and isset($_POST["pwd"])){
                    if(authenticateLogin($_POST["usr"],$_POST["pwd"])){
                        header('Location:userprofile.php');
                        die($msg["slogin"]);
                    }
                }
                header("Location:loginregister.html");
                die($msg["flogin"]);
            }
            register();
        }
        header("Location:loginregister.html");
        die($msg["nlogin"]);
    }
}

function logout(){
    global $msg;
    if(isset($_SESSION['user'])) unset($_SESSION['user']);
    header("Location:loginregister.html");
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

function checkSet($param,$type){
    foreach($param as $arg){
        if($type=='G'){
            if(!isset($_GET[$arg])) return false;
        }
        else{
            if(!isset($_POST[$arg])) return false;
        }
    }
    return true;
}
function register(){
  global $msg,$srv,$DB;
  try{
    if(!isset($_SESSION['user'])){
        if(isset($_GET["service"]) and $_GET["service"] == "register"){
            if($_GET["service"] == $srv["register"] and checkSet(['fname','lname','flat','street','city','country','dob','sex','email','username','pwd','cpwd'],'P')){
                $pwd = $_POST['pwd'];
                $cpwd = $_POST['cpwd'];
                $fname = $_POST['fname'];
                $lname = $_POST['lname'];
                $flat = $_POST['flat'];
                $street = $_POST['street'];
                $city = $_POST['city'];
                $state = $_POST['state'];
                $country = $_POST['country'];
                $email = $_POST['email'];
                $sex = $_POST['sex'];
                $dob = $_POST['dob'];
                $username = $_POST['username'];
                if($pwd != $cpwd) throw new Exception();
                if(isset($_FILES['ppic'])){
                    $type = $_FILES['ppic']['type'];
                    $size = $_FILES['ppic']['size'];
                    $path = $_FILES['ppic']['tmp_name'];
                    if(substr($type,0,5) == 'image' and $size < 500000){
                        $img = base64_encode(file_get_contents($path));
                        $query = "insert into Users
                                    (passwd,first_name,last_name,flat_no,street,city,state,country,email,sex,dob,username,profile_id,profilepic) values
                                    ('$pwd','$fname','$lname','$flat','$street','$city','$state','$country','$email','$sex','$dob','$username','0','$img');";
                        $DB->query($query);
                        }
                    else{
                        header("Location:loginregister.html/#toregister");
                        die($msg["fregister"]);
                    }
                }
                else{
                    /* Name Initials Feature to be added later */
                }
                header("Location:index.php");
                die($msg["sregister"]);
            }
        }
    }
  }
    catch(Exception $exc){}
    header("Location:loginregister.html#toregister");
    die($msg["fregister"]);
}
  
function deregister(){
    
}

  
function requestPage(){
  
}

function search(){
    global $DB;
    if(!isset($_GET['query'])) die();
    $querystr = $_GET['query'];
    try{
        $query = "SELECT username,first_name,last_name FROM Users WHERE CONCAT(first_name,' ',last_name) LIKE '$querystr%'";
        $out = [];
        foreach($DB->query($query)->fetchAll() as $result) array_push($out,["username"=>$result["username"],"first_name"=>$result["first_name"],"last_name"=>$result["last_name"]]);
        echo(json_encode($out));
    } catch(PDOException $e){}
    die();
}

secureGET();
securePOST();
connectDB();
login();

if(isset($_GET["service"])){
    switch($_GET["service"]){
        case $srv['logout']:
            logout();
            break;
        case $srv['login']:
            header('Location:userprofile.php');
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
        case $srv['search']:
            search();
            break;
        default:
            header('loginregister.html');
            die('[FAIL] Unrecognized service requested');
    }
}
else{
    header('Location:userprofile.php');
    die("No service availed");
}
?>