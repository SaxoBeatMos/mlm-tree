<?
session_start();
include "mlmtree3.php"; // class file
//-------------------------Database Init
$user='';
$password='';
$dbname='';

if($_SERVER['REMOTE_ADDR'] == '::1' || $_SERVER['REMOTE_ADDR'] == '127.0.0.1' )
$mlm= new newmlmtree('localhost',$user,$password,$dbname) ;
else
$mlm= new newmlmtree('localhost',$user,$password,$dbname) ;
$op=array();

//---------------------------Left Bar

if($mlm->_lc()){
$op['leftcontent'] = '<li class="list-group-item"><a href="?q='.$mlm->encode('q=showtree').'" >Binary Tree</a></li>';
$op['leftcontent'] .= '<li class="list-group-item"><a href="?q='.$mlm->encode('q=profile').'" >Profile</a></li>';
$op['leftcontent'] .= '<li class="list-group-item"><a href="?q='.$mlm->encode('q=logout').'" >LogOUT</a></li>';
$lc=true;
$op['maincontent']='<div class="alert alert-success" role="alert">Welcome Member Area !</div>';
}
else
{
$lc=false;
$op['leftcontent']='<li class="list-group-item"><a href="?q='.$mlm->encode('q=login').'" >LogIN</a></li>';
$op['leftcontent'] .= '<li class="list-group-item"><a href="?q='.$mlm->encode('q=newmember').'" >New Member</a></li>';
$op['maincontent']='<div class="alert alert-warning" role="alert">Welcome!</div>';
}

//-------------------------Query 

if(isset($_GET['q'])){
$ps=$mlm->decode($_GET['q']); 
if(mb_detect_encoding($ps, 'UTF-8', true))
parse_str($ps);
else
$op['maincontent'] .= '<div class="alert alert-info" role="alert">404 File Not Found</div>';
}



//--------------route------------------------------
if(isset($_POST['ok']) && $_POST['ok'] == 'New Member')
$op['maincontent'] .= $mlm->_newMember($_POST,$mlm->tablemlm);
elseif(isset($_POST['ok']) && $_POST['ok'] == 'Profile')
$op['maincontent'] .= $mlm->_profile($_POST,$mlm->tablereg);
elseif(isset($_POST['ok']) && $_POST['ok'] == 'Update')
$op['maincontent'] .= $mlm->_updateMember($_POST);
elseif(isset($_POST['ok']) && $_POST['ok'] == 'Delete')
$op['maincontent'] .= $mlm->_DeleteMember($_POST);
elseif(isset($_POST['ok']) && $_POST['ok'] == 'Login')
$op['maincontent'] .= $mlm->_login($_POST,$mlm->tablemlm);
elseif(isset($q) && $q == 'newmember')
{
$op['maincontent'] .= $mlm->newMember(isset($_POST) ? $_POST : '' );
}
elseif(isset($q) && $q == 'showtree' && $lc)
{
	if(isset($add)){
	$_POST['remail']=$add;
	$op['maincontent'] .= $mlm->newMember(isset($_POST) ? $_POST : '' );
	}
	elseif(isset($edit)){
		$r=$mlm->fpid($edit,'Update');
	$op['maincontent'] .= $mlm->newMemberUPdate(!empty($r) ? $r : '' );
	}
	elseif(isset($del)){
			$r=$mlm->fpid($del,'Delete');
	$op['maincontent'] .= $mlm->newMember(!empty($r) ? $r : '' );
	}
	
	
$op['footer'] = isset($email) ? $mlm->showtree($email) : $mlm->showtree();
$op['maincontent'] .="<div id='chart_div'>";
}
elseif(isset($q) && $q == 'login')
{
$op['maincontent'] .= $mlm->login(isset($_POST) ? $_POST : '' );
}
elseif(isset($q) && $q == 'profile' && $lc)
{
$op['maincontent'] .= $mlm->profile(isset($_POST) ? $_POST : '' );
}
elseif(isset($q) && $q == 'logout' && $lc)
{
$op['maincontent'] .= $mlm->logout();
}

//---------------------------------------------

if(is_file("./template/form-template.html")){
			echo $mlm->render_temp("./template/form-template.html",$op);
	}	
