<?
/*
Author : VISHV SAHDEV
Website : V23.IN
Email : vishv23@gmail.com
Date : 18 Dec. 2015
Keyword : MLM Tree | Organisation Tree | Binary Tree | Unlimited Tree | 2x2 Binary | 3x3 Binary | 4x4 Binary
Version : 3.0
*/


/*
Example
$mlm= new newmlmtree('localhost','root','','v23') ;
$mlm->matrix = 2;  //  2x2 binary tree  | 3x3 |  unlimited -1
$mlm->level = 3;   // step show in tree
$mlm->adminEmail=''; // root access
$mlm->seo = true ;
$mlm->href('?key=localhost&value=root'); // output /localhost/root
$mlm->encode('vishvsahdev');  //T7mV3zZprpZf8XJjqUO
$mlm->decode('T7mV3zZprpZf8XJjqUO');  // vishvsahdev
$mlm->showtree('root@v23.in')  
$mlm->profile($_POST);  // profile
.
.
.
+
+
*/
class newmlmtree{

var $link;
var $debug=true;
var $ar=array();
var $tablemlm;
var $tablereg;
var $content='';
var $tree=array();
var $matrix=2;
var $seo;
var $level=3;
var $adminEmail='root@v23.in';
//----------------------------------------------------------Database

public function __construct($host, $user, $pass, $db) 
{
$this->link = mysqli_connect($host, $user, $pass, $db);
	if (!$this->link && $this->debug) {
	echo "Error: Unable to connect to MySQL." . PHP_EOL;
	echo "Debugging errno: " . mysqli_connect_errno() . PHP_EOL;
	echo "Debugging error: " . mysqli_connect_error() . PHP_EOL;
	exit;
}	

$this->tablemlm='mlmpassword';
$this->tablereg='mlmregistration';
}
	
public function __destruct()
{
mysqli_close($this->link);
}

public function href($link)
{
if($this->seo){
$st='';
foreach(explode("&",parse_url($link, PHP_URL_QUERY)) as $kk){
if(count($r=explode("=", $kk)) == 2){
 list($key, $value) = $r; 
$st .= "/$value";
}
}
$withoutExt = preg_replace('/\\.[^.\\s]{3,4}$/', '', parse_url($link, PHP_URL_PATH));
return $withoutExt.$st;
}
return $link;
}

public function encode($r){
$r=base64_encode($r);
$n="ABCDEFGHIJKLMNOPQRSTUVWXYZabcedfghijklmnopqrstuvwxyz0123456789=";
$convert = 'YsKjeXT3iz0uwLFtWHr6v28R-pGfOCVcPbD4gZNAIJ5EnkMahdUQBq9mlSy1x7o';
$numbers1 = str_split($r);
$f=array_combine(str_split($n), str_split($convert));
$x='';
foreach($numbers1 as $k=>$v)
if(isset($f[$v]))
$x .= $f[$v];
else
$x .= $v;
return $x;

}

public function decode($r){
$n="ABCDEFGHIJKLMNOPQRSTUVWXYZabcedfghijklmnopqrstuvwxyz0123456789=";
$convert = 'YsKjeXT3iz0uwLFtWHr6v28R-pGfOCVcPbD4gZNAIJ5EnkMahdUQBq9mlSy1x7o';
$numbers1 = str_split($r);
$f=array_combine(str_split($n), str_split($convert));
$f=array_flip($f);
$x='';
foreach($numbers1 as $k=>$v){
if(isset($f[$v]))
$x .= $f[$v];
else
$x .= $v;
}
return base64_decode($x);
}
	
//------------------------------TREE
	
public function showtree($email=null)
{
if($e=$this->_lc()){
if($e->email == $this->adminEmail){
	if(!empty($email) && $q=$this->query($this->tablemlm,'id,rID, pid, email'," WHERE email = '$email' LIMIT 1"))
		$e=$q[0];
}
else{		
			if(!empty($email) && $q=$this->query($this->tablemlm,'id,rID, pid, email'," WHERE email = '$email' AND rID = $e->rID LIMIT 1"))
				$e=$q[0];
}


$r=$this->_tree($e->id);
$v='data.addRows([';
$v .= '[{v:\''.$e->id.'\',f:\''.$e->email.'<br><a  href="?q='.$this->encode('q=showtree&add='.$e->email).'"> New <\/a> \'},\''.$e->pid.'\',\''.$e->email.'\'],';

foreach($r as $row)
$v .= '[{v:\''.$row['id'].'\',f:\'<a class="link"  title="downlink show" href="?q='.$this->encode('q=showtree&email='.$row['email']).'">'.$row['email'].'</a><br><a  href="?q='.$this->encode('q=showtree&add='.$row['email']).'">New<\/a> <a  href="?q='.$this->encode('q=showtree&edit='.$row['email']).'">&nbsp|&nbspEdit&nbsp|&nbsp<\/a> <a  href="?q='.$this->encode('q=showtree&del='.$row['email']).'">Del<\/a>\'},\''.$row['pid'].'\',\''.$row['email'].'\'],';
$v .= ']);';
	if(is_file("./template/mlm_chart.html"))
	return $this->render_temp("./template/mlm_chart.html",array("value"=>$v));	
	else
	return false;
}
return false;
}

private function _CHECKrefID($id = 0) {
if($q=$this->query($this->tablemlm,'id,rID, count(pid) as cpid'," WHERE pid = $id ")){
if($q[0]->cpid >= $this->matrix)
return $this->_GETrefID($q[0]->rID);
}
return $id;
}

private function _GETrefID($id = 0) {
if($q=$this->query($this->tablemlm,' person.id, person.email, count(child.id) AS number_of_children','AS person LEFT JOIN `mlmpassword` AS child on person.id = child.pid where person.rID = '.$id.' GROUP BY person.id HAVING number_of_children < '.$this->matrix.' LIMIT 1'))
return $q[0]->id;
}
	
private function _tree($id = 1,$counter = 0) {
if($q=$this->query($this->tablemlm,'*','WHERE `pid`='.$id.' '))
{
$counter ++;
if($counter < $this->level){
   foreach($q as $row) {
 
   if($row->id != $row->pid ){
      $this->tree[] = array("id" => $row->id,"pid" => $row->pid,"email"=>$row->email);
	 
      $this->tree = self::_tree($row->id, $counter);
	} 
    }
  }
  }
  return $this->tree;
}

//-------------------------- MEMBER LOGIN PROFILE 


	
	
public function profile($p='')
{
if(!is_array($p))
$p=array();
$ar=array("name"=>'',"address"=>'',"phone"=>'',"mobile"=>'',"country"=>'',"state"=>'',"email"=>'',"pincode"=>'',"city"=>'');
if($row=$this->query($this->tablereg,'*','WHERE md= \''.$_SESSION['ok'] . '\' LIMIT 1')){
$ar=$this->_dataTransfer($ar,(array)$row[0]);
}
else
$ar=$this->_dataTransfer($ar,$p);
$r=$this->formStart($_SERVER['PHP_SELF']);
$r .= $this->hiddenField("ok","Profile");
$r .= $this->textField('Name','name','',$ar['name']);
$r .= $this->textAreaField('Address','address',$ar['address']);
$r .= $this->textField('Phone','phone','',$ar['phone']);
$r .= $this->textField('Mobile','mobile','',$ar['mobile']);
$r .= $this->selectField('Country','country',$this->country(),$ar['country']);
$r .= $this->textField('State','state','',$ar['state']);
$r .= $this->textField('city','city','',$ar['city']);
$r .= $this->textField('Pincode','pincode','',$ar['pincode']);
$r .= $this->textField('Email','email','',$ar['email']);

$r .= $this->buttonField('UPDATE','info');
$r .= $this->formEnd();
return $r;
}		
	
public function _profile($p='')
{
if(!is_array($p))
$p=array();
$ar=array("name"=>'',"address"=>'',"phone"=>'',"mobile"=>'',"country"=>'',"state"=>'',"email"=>'',"pincode"=>'',"city"=>'');
$ar=$this->_dataTransfer($ar,$p);	
if(!$this->update($ar,$this->tablereg,"md='".$_SESSION['ok']."'")){
	$this->content = '<div class="alert alert-danger" role="alert">Error data...</div>';
	$this->content .= $this->profile($p);
	}
	else
return '<div class="alert alert-success" role="alert">Update </div>' . $this->profile($p);
}	
	
public function login($p='')
{
if(!is_array($p))
$p=array();
$ar=array("email"=>'',"password"=>'');
$ar=$this->_dataTransfer($ar,$p);
$r=$this->formStart($_SERVER['PHP_SELF']);
$r .= $this->hiddenField("ok","Login");
$r .= $this->textField('Email','email','',$ar['email']);
$r .= $this->textField('Password','password','password',$ar['password']);
$r .= $this->buttonField('Submit','info');
$r .= $this->formEnd();
return $r;
}	
	
	
public function _login($p='')
{
if(!is_array($p))
$p=array();
$ar=array("email"=>'',"password"=>'');
$ar=$this->_dataTransfer($ar,$p);
if($row=$this->query($this->tablemlm,'*','WHERE '.$this->check($ar) . ' LIMIT 1')){
$_SESSION['ok']=$row[0]->md;
$_SESSION['id']=$row[0]->id;
$_SESSION['pid']=$row[0]->pid;
$_SESSION['email']=$row[0]->email;
header('Location: '. $_SERVER['PHP_SELF']);
exit;
}
else
return '<div class="alert alert-danger" role="alert">Ivalid login.</div>' . $this->login($p);
}	
	

public function newMember($p='')
{
if(!is_array($p))
$p=array();

$ar=array("remail"=>'root@v23.in',"email"=>'',"password"=>'',"repassword"=>'',"ok"=>'New Member');
$ar=$this->_dataTransfer($ar,$p);
$r=$this->formStart($_SERVER['PHP_SELF']);
$r .= $this->hiddenField("ok",$ar['ok']);
$r .= $this->textField('Refrence Email ID','remail','',$ar['remail']);
$r .= $this->textField('Email','email','',$ar['email']);
$r .= $this->textField('Password','password','password',$ar['password']);
$r .= $this->textField('Re-Enter Password','repassword','password',$ar['repassword']);
$r .= $this->buttonField($ar['ok'],'info');
$r .= $this->formEnd();
return $r;
}
	
	

public function newMemberUPdate($p='')
{
if(!is_array($p))
$p=array();

$ar=array("remail"=>'root@v23.in',"oldemail"=>'',"email"=>'',"password"=>'',"repassword"=>'',"ok"=>'New Member');
$ar=$this->_dataTransfer($ar,$p);
$r=$this->formStart($_SERVER['PHP_SELF']);
$r .= $this->hiddenField("ok",$ar['ok']);
//$r .= $this->hiddenField("remail",$ar['remail']);
$r .= $this->hiddenField("oldemail",$ar['email']);
$r .= $this->textField('Refrence Email ID','remail','',$ar['remail']);
$r .= $this->textField('Email','email','',$ar['email']);
$r .= $this->textField('Password','password','password',$ar['password']);
$r .= $this->textField('Re-Enter Password','repassword','password',$ar['repassword']);
$r .= $this->buttonField($ar['ok'],'info');
$r .= $this->formEnd();
return $r;
}	

private function _MemberV($p='')
{
if(!is_array($p))
$p=array();
$r=array();
$arold=array("error"=>true,"remail"=>'root@v23.in',"oldemail"=>'',"email"=>'',"password"=>'',"repassword"=>'',"ok"=>'New Member');
$ar=$this->_dataTransfer($arold,$p);
$datain=array("email"=>"email","password"=>'='.$ar['repassword'].'|alphaall|lt:15');

if($r[1]=$this->_findValid($datain,$p)){
$r['error']=false;
return $r;
}
return $ar;
}
public function ecode($error)
{
	$ecode[0]="Email ID already exists...";
	$ecode[1]="Error data...";
	$ecode[2]="Error data...";
	$ecode[3]="Update data...";
	$ecode[4]="Save data...";
	$ecode[5]="Parent Id ...";
	$ecode[6]="Error try again..";
	$ecode[7]="Delete..";
	$ecode[8]="Unable Delete..";
	$ecode[9]="Unable Delete.. Child ID found";
	$ecode[10]="Child > matrix";
	$ecode[11]="--Admin Root--";
return isset($ecode[$error]) ? $ecode[$error] : 'New error...';
}
	
private function _getLdetail($key,$value)
{
is_numeric($value) ? $c = $value : $c = "'".$value."'";
if($r=$this->query($this->tablemlm,'*'," WHERE $key = $c LIMIT 1" ))	
return $r[0];
}

	
private function _getFirstRow($id)
{
if($r=$this->query($this->tablemlm,$id," ORDER BY ASC LIMIT 1" ))	
return $r[0]->$id;
else
return 0;
}	

	
public function _newMember($p='')
{
$old = $this->_MemberV($p);
if(!$old['error'])
return '<div class="alert alert-danger" role="alert">'.$old[1].'</div>' . $this->newMember($p);	

$ar=array("email"=>'',"password"=>'',"ip"=>$_SERVER['REMOTE_ADDR'],'datetime'=>date("Y-m-d H:i:s"),'status'=>'1','md'=>$this->uid());
$ar=$this->_dataTransfer($ar,$p);
if($row=$this->_getLdetail('email',$old['remail']))
$ar['pid']=$row->id;
else
$ar['pid']=$this->_getFirstRow('id');
	
$ar['pid']=$this->_CHECKrefID($ar['pid']);
$ar["rID"]= isset($_SESSION['id']) ? $_SESSION['id'] : $ar['pid'];

if($this->_getLdetail('email',$ar['email']))
return '<div class="alert alert-danger" role="alert">'.$this->ecode(0).'</div>' . $this->newMember($p);
	
	if(!$this->insert($ar,$this->tablemlm)){
	$this->content = '<div class="alert alert-danger" role="alert">'.$this->ecode(1).'</div>';
	$this->content .= $this->newMember($p);
	}
	else
	{
	$lid=$this->link->insert_id;
	$this->content = '<div class="alert alert-success" role="alert"><ul><li>'.$this->ecode(5).''.$ar['pid'].'</li></ul></div>';
	$this->insert(array("email"=>$ar['email'],"md"=>$ar['md'],"pid"=>$lid),$this->tablereg);
	}
	
return $this->content;
}

	
public function _updateMember($p='')
{
$old = $this->_MemberV($p);
if(!$old['error'])
return '<div class="alert alert-danger" role="alert">'.$old[1].'</div>' . $this->newMember($p);	

$ar=array("email"=>'',"password"=>'');
$ar=$this->_dataTransfer($ar,$p);
if($r1=$this->_getLdetail('email',$ar['email'])){
	if($r1->email != $old['oldemail'] )
		return '<div class="alert alert-danger" role="alert">'.$this->ecode(0).'</div>' . $this->newMemberUPdate($p);
unset($r1);		
}
$ex=false;	
if($r1=$this->_getLdetail('email',$old['oldemail'])){
	if($old['remail'] != $_SESSION['email'] && $r2=$this->query($this->tablemlm,'rID,id,pid'," WHERE email = '".$old['remail']."' AND rID = '".$_SESSION['id']."' LIMIT 1" )){
		if($r3=$this->query($this->tablemlm,'count(pid) as cpid'," WHERE pid = ".$r2[0]->id." AND rID = '".$_SESSION['id']."' LIMIT 1" )){		
			if($r3[0]->cpid < $this->matrix){
				$ar['pid']=$r2[0]->id;
				$ex ='<div class="alert alert-success" role="alert">'.$this->ecode(3).''.$r1->id .' - '.$ar['email'].'</div>';
				}
				else
				$ex='<div class="alert alert-success" role="alert">'.$this->ecode(10).''.$r1->id .' - '.$ar['email'].'</div>';
		}
		
}
	
				if($this->update($ar,$this->tablemlm," id='".$r1->id."' LIMIT 1"))
				return $ex;
				else
				return '<div class="alert alert-danger" role="alert">'.$this->ecode(2).''.$r1->id .' - '.$ar['email'].'</div>';
			}
			else
			return '<div class="alert alert-danger" role="alert">'.$this->ecode(6).''.$ar['email'].'</div>';
			}	

	
public function _DeleteMember($p='')
{
if(isset($p['email']))
$ar['email']=$p['email'];
else
return '<div class="alert alert-danger" role="alert">'.$this->ecode(8).'</div>';

	if($r=$this->query($this->tablemlm,'id,pid'," WHERE email = '".$ar['email']."' LIMIT 1" )){
		if($rt=$this->query($this->tablemlm,'count(pid) as cpid'," WHERE pid = '".$r[0]->id."'  AND rID = '".$_SESSION['id']."' LIMIT 1" )){
			if($rt[0]->cpid > 0)
			return '<div class="alert alert-danger" role="alert">'.$this->ecode(9).'</div>';
			else{
				if($_SESSION['email'] == $ar['email'])
				return '<div class="alert alert-danger" role="alert">'.$this->ecode(11).''.$ar['email'].'</div>';
				else
				$this->link->query('DELETE FROM '.$this->tablemlm.'  WHERE email=\''.$ar['email'].'\' LIMIT 1');
				return '<div class="alert alert-danger" role="alert">'.$this->ecode(7).''.$ar['email'].'</div>';
			}
		}	
}
else
return '<div class="alert alert-danger" role="alert">'.$this->ecode(8).'</div>';
}
	


//----------------------------TEMPLATE -----------------

public function render_temp($file, $data = array()) {
	if (file_exists($file)) {
		extract($data);
		ob_start();
		require($file);
		$out = ob_get_contents();
		ob_end_clean();
		return $out;
	} else {
	return false;
	}
}

		
//------------------------------------------------------
	
	
	

public function _dataTransfer($dataIN,$dataOUT,$keyValue='key')
{
if(!is_array($dataIN))
return false;
foreach($dataIN as $k=>$v){
	if($keyValue == 'value'){
		if(isset($dataOUT[$v]))
		$dataIN[$v]=$dataOUT[$v];
		}
		else
		if(isset($dataOUT[$k]))
		$dataIN[$k]=$dataOUT[$k];
	}
return $dataIN;
}
public function fpid($email,$status)
	{
	if($r=$this->query($this->tablemlm,'pid,email,password'," WHERE email = '".$email."' LIMIT 1" )){
	$o['email']=$r[0]->email;
		$o['password']=$r[0]->password;
		$o['repassword']=$r[0]->password;
		$o['ok']=$status;
	
	if($r=$this->query($this->tablemlm,'email'," WHERE id = '".$r[0]->pid."' LIMIT 1" )){
		$o['remail']=$r[0]->email;
		}
	return $o;	
	}
	return false;
	}

//-----------------------------------SESSION VALID LOGOUT -------------
	
public function _lc()
{
if(isset($_SESSION['ok']))
$o=$_SESSION['ok'];
else
return false;
if($row=$this->query($this->tablemlm,'*','WHERE md = \''.$o.'\' LIMIT 1'))
return $row[0];
else
return false;
}	
	

function logout()
{
unset($_SESSION['ok']);
session_destroy();
header('Location: '. $_SERVER['PHP_SELF']);
exit;
}		

//--------------------------------UNIQUE ID----

public function uid() {
	return sprintf( '%04x-%04x-%04x-%04x',mt_rand( 0, 0x0fff ) | 0x4000,mt_rand( 0, 0x3fff ) | 0x8000,mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ));
}

	
//--------------------------------UPDATE INSERT	QUERY


public function query($table, $id,$where) {
$sql = "SELECT $id FROM `$table` $where";
	if($result = $this->link->query($sql)){
	while ( $row = $result->fetch_object() )
	$results[] = $row;
	return isset($results) ? $results : false;
	}
return false;
}


public function check($data){
if(!is_array($data))
	$data=array();
	foreach ($data as $k=>$v)
		{
		isset($vals) ? $vals .= ' AND ' : $vals = '';
		$vals .= $k." = '".$this->link->real_escape_string($v)."'";
		}
return $vals;
}	
	
public function insert($data,$tablename)
{
if(!is_array($data))
return false;
$cols = implode('`, `', array_keys($data));
	foreach (array_values($data) as $value)
	{
	isset($vals) ? $vals .= ',' : $vals = '';
	if(!is_numeric($value))
	$vals .= '\''.$this->link->real_escape_string($value).'\'';
	else
	$vals .= $value;
	}
return $this->sqlquery('INSERT INTO `'.$tablename.'` (`'.$cols.'`) VALUES ('.$vals.')');
}

public function sqlquery($sql)
{
if($this->link->query($sql) == 1)
return true;
else
return false;
}	
	
public function update($data,$tablename,$where){
if(!is_array($data))
return false;	
	foreach ($data as $k=>$v)
	{
	isset($vals) ? $vals .= ',' : $vals = '';
	if(!is_numeric($v))
	$vals .= '`'.$k.'` = \''.$this->link->real_escape_string($v).'\'';
	else
	$vals .= '`'.$k.'` = '.$v;
	}	
return $this->sqlquery('UPDATE `'.$tablename.'` SET '.$vals.' WHERE '.$where.'');
}
	


//-------------------------FORM FUNCTION
	
	
public function textField($label,$name,$type='text', $default=''){
if(!preg_match('/text|email|radio|checkbox|color|password/i', $type))
$type='text';
return '
<div class="form-group">
<label   class="col-sm-3 control-label">'.$label.'</label>
<div class="col-sm-9">      
<input type="'.$type.'" class="form-control" name="'.$name.'" id="'.$name.'" value="'.$default.'" />
</div>
</div>'."\n";
}
	
public function buttonField($name,$color='primary'){
if(!preg_match('/default|primary|success|info|warning|danger/i', $color))
$color='default';
return '
<div class="form-group">
<div class="col-sm-offset-2 col-sm-9">
<button type="submit" class="btn btn-'.$color.'">'.$name.'</button>
</div>
</div>'."\n";
}

public function hiddenField($name,$value){
return '<input type="hidden" name="'.$name.'" value="'.$value.'" />'."\n";
}

public function formStart($action,$method='POST',$name='ok'){
return '<form class="form-horizontal" action="'.$action.'" method="'.$method.'" id="'.$name.'">'."\n";
}
public function textareaField($label,$name,$value=''){
return '
<div class="form-group">
<label   class="col-sm-3 control-label">'.$label.'</label>
<div class="col-sm-9">    
<textarea class="form-control" rows="3" name="'.$name.'" id="'.$name.'">'.$value.'</textarea>
</div>
</div>'."\n";
}	
	
public function formEnd(){
return '</form>'."\n";
}

public function selectField($label,$name, $value=array(),$default=''){
$r ='
<div class="form-group">
<label   class="col-sm-3 control-label">'.$label.'</label>
<div class="col-sm-9">      
<select  class="form-control" name="'.$name.'" id="'.$name.'">';
if(is_array($value)){
	foreach($value as $k=>$v){
		if($k === $default)
		 $r .= "<option value='$k' selected>$v</option>\n";
		 else
		 $r .= "<option value='$k'>$v</option>\n";
	}
}
$r .='</select>
</div>
</div>'."\n";
return $r;
}

//-------------------------------------------FORM VALIDATION RULE------------

public function _findValid($datain,$dataout)
{
$error=false;
	foreach($datain as $k=>$v){
		if(isset($dataout[$k])){
		if($r=$this->_valid($dataout[$k],$v))
		$error .= $r;
		}
	}
if($error)
return	"<ul>$error</ul>";
else
return false;
}


public function _valid($str,$rule)
{
$op='';
foreach(explode("|",$rule) as $r)
$op .= $this->valid($str,$r);
return $op;
}

public function valid($str,$rule)
{ 

if($rule=="digit"){
	if(preg_match('/^[0-9]+$/',$str))
	return false;
	else
	return "<li>The string $str does not consist of all digits.</li>";
}
elseif(substr($rule,0,1) == "="){
	if($str == substr($rule,1))
	return false;
	else 
	return "<li>field mismatched...</li>";
}
elseif($rule=="alphasmall"){
	if(preg_match('/^[a-z]+$/',$str))
	return false;
	else
	return "<li>Only all small latters ...</li>";
}
elseif($rule=="alpha"){
	if(preg_match('/^[a-zA-Z]+$/',$str))
	return false;
	else
	return "<li>Only all upper or small alpha (A-Z) chars ...</li>";
}
elseif($rule=="alphanum"){
	if(preg_match('/^[a-zA-Z0-9]+$/',$str))
	return false;
	else
	return "<li>Only Alpha  Numeric chars ...</li>";
}
elseif($rule=="alphaall"){
if(preg_match('/^[A-Za-z0-9_.\-+ ]+$/',$str))
return false;
	else
	return "<li>all chars (a-z) (A-Z) (0-9) (_.- )</li>";
}	
elseif($rule=="email"){
if(preg_match('/^[a-zA-Z0-9_\.\-]+@[a-zA-Z0-9\-]+\.[a-zA-Z0-9\-\.]+$/',$str)) 
return false;
	else
	return "<li>Valid Email ID</li>";
}	
elseif(substr($rule,0,3) == "lt:"){
if(strlen($str) <= substr($rule,3))
		return false;
	else
	return "<li>$str <= ".substr($rule,3)."</li>";
}			
elseif($rule=="name"){
if(preg_match('/^[a-zA-Z]+(([\',. -][a-zA-Z ])?[a-zA-Z]*)*$/',$str))
return false;
	else
	return "<li>Valid Name</li>";
}	

else
return false;
}
	

//-------------------------------------------COUNTRY ARRAY -------------------	
	
 public function country(){

return array(
'AF'=>'Afghanistan',
'AL'=>'Albania',
'DZ'=>'Algeria',
'AS'=>'American Samoa',
'AD'=>'Andorra',
'AO'=>'Angola',
'AI'=>'Anguilla',
'AQ'=>'Antarctica',
'AG'=>'Antigua And Barbuda',
'AR'=>'Argentina',
'AM'=>'Armenia',
'AW'=>'Aruba',
'AU'=>'Australia',
'AT'=>'Austria',
'AZ'=>'Azerbaijan',
'BS'=>'Bahamas',
'BH'=>'Bahrain',
'BD'=>'Bangladesh',
'BB'=>'Barbados',
'BY'=>'Belarus',
'BE'=>'Belgium',
'BZ'=>'Belize',
'BJ'=>'Benin',
'BM'=>'Bermuda',
'BT'=>'Bhutan',
'BO'=>'Bolivia',
'BA'=>'Bosnia And Herzegovina',
'BW'=>'Botswana',
'BV'=>'Bouvet Island',
'BR'=>'Brazil',
'IO'=>'British Indian Ocean Territory',
'BN'=>'Brunei',
'BG'=>'Bulgaria',
'BF'=>'Burkina Faso',
'BI'=>'Burundi',
'KH'=>'Cambodia',
'CM'=>'Cameroon',
'CA'=>'Canada',
'CV'=>'Cape Verde',
'KY'=>'Cayman Islands',
'CF'=>'Central African Republic',
'TD'=>'Chad',
'CL'=>'Chile',
'CN'=>'China',
'CX'=>'Christmas Island',
'CC'=>'Cocos (Keeling) Islands',
'CO'=>'Columbia',
'KM'=>'Comoros',
'CG'=>'Congo',
'CK'=>'Cook Islands',
'CR'=>'Costa Rica',
'CI'=>'Cote D\'Ivorie (Ivory Coast)',
'HR'=>'Croatia (Hrvatska)',
'CU'=>'Cuba',
'CY'=>'Cyprus',
'CZ'=>'Czech Republic',
'CD'=>'Democratic Republic Of Congo (Zaire)',
'DK'=>'Denmark',
'DJ'=>'Djibouti',
'DM'=>'Dominica',
'DO'=>'Dominican Republic',
'TP'=>'East Timor',
'EC'=>'Ecuador',
'EG'=>'Egypt',
'SV'=>'El Salvador',
'GQ'=>'Equatorial Guinea',
'ER'=>'Eritrea',
'EE'=>'Estonia',
'ET'=>'Ethiopia',
'FK'=>'Falkland Islands (Malvinas)',
'FO'=>'Faroe Islands',
'FJ'=>'Fiji',
'FI'=>'Finland',
'FR'=>'France',
'FX'=>'France, Metropolitan',
'GF'=>'French Guinea',
'PF'=>'French Polynesia',
'TF'=>'French Southern Territories',
'GA'=>'Gabon',
'GM'=>'Gambia',
'GE'=>'Georgia',
'DE'=>'Germany',
'GH'=>'Ghana',
'GI'=>'Gibraltar',
'GR'=>'Greece',
'GL'=>'Greenland',
'GD'=>'Grenada',
'GP'=>'Guadeloupe',
'GU'=>'Guam',
'GT'=>'Guatemala',
'GN'=>'Guinea',
'GW'=>'Guinea-Bissau',
'GY'=>'Guyana',
'HT'=>'Haiti',
'HM'=>'Heard And McDonald Islands',
'HN'=>'Honduras',
'HK'=>'Hong Kong',
'HU'=>'Hungary',
'IS'=>'Iceland',
'IN'=>'India',
'ID'=>'Indonesia',
'IR'=>'Iran',
'IQ'=>'Iraq',
'IE'=>'Ireland',
'IL'=>'Israel',
'IT'=>'Italy',
'JM'=>'Jamaica',
'JP'=>'Japan',
'JO'=>'Jordan',
'KZ'=>'Kazakhstan',
'KE'=>'Kenya',
'KI'=>'Kiribati',
'KW'=>'Kuwait',
'KG'=>'Kyrgyzstan',
'LA'=>'Laos',
'LV'=>'Latvia',
'LB'=>'Lebanon',
'LS'=>'Lesotho',
'LR'=>'Liberia',
'LY'=>'Libya',
'LI'=>'Liechtenstein',
'LT'=>'Lithuania',
'LU'=>'Luxembourg',
'MO'=>'Macau',
'MK'=>'Macedonia',
'MG'=>'Madagascar',
'MW'=>'Malawi',
'MY'=>'Malaysia',
'MV'=>'Maldives',
'ML'=>'Mali',
'MT'=>'Malta',
'MH'=>'Marshall Islands',
'MQ'=>'Martinique',
'MR'=>'Mauritania',
'MU'=>'Mauritius',
'YT'=>'Mayotte',
'MX'=>'Mexico',
'FM'=>'Micronesia',
'MD'=>'Moldova',
'MC'=>'Monaco',
'MN'=>'Mongolia',
'MS'=>'Montserrat',
'MA'=>'Morocco',
'MZ'=>'Mozambique',
'MM'=>'Myanmar (Burma)',
'NA'=>'Namibia',
'NR'=>'Nauru',
'NP'=>'Nepal',
'NL'=>'Netherlands',
'AN'=>'Netherlands Antilles',
'NC'=>'New Caledonia',
'NZ'=>'New Zealand',
'NI'=>'Nicaragua',
'NE'=>'Niger',
'NG'=>'Nigeria',
'NU'=>'Niue',
'NF'=>'Norfolk Island',
'KP'=>'North Korea',
'MP'=>'Northern Mariana Islands',
'NO'=>'Norway',
'OM'=>'Oman',
'PK'=>'Pakistan',
'PW'=>'Palau',
'PA'=>'Panama',
'PG'=>'Papua New Guinea',
'PY'=>'Paraguay',
'PE'=>'Peru',
'PH'=>'Philippines',
'PN'=>'Pitcairn',
'PL'=>'Poland',
'PT'=>'Portugal',
'PR'=>'Puerto Rico',
'QA'=>'Qatar',
'RE'=>'Reunion',
'RO'=>'Romania',
'RU'=>'Russia',
'RW'=>'Rwanda',
'SH'=>'Saint Helena',
'KN'=>'Saint Kitts And Nevis',
'LC'=>'Saint Lucia',
'PM'=>'Saint Pierre And Miquelon',
'VC'=>'Saint Vincent And The Grenadines',
'SM'=>'San Marino',
'ST'=>'Sao Tome And Principe',
'SA'=>'Saudi Arabia',
'SN'=>'Senegal',
'SC'=>'Seychelles',
'SL'=>'Sierra Leone',
'SG'=>'Singapore',
'SK'=>'Slovak Republic',
'SI'=>'Slovenia',
'SB'=>'Solomon Islands',
'SO'=>'Somalia',
'ZA'=>'South Africa',
'GS'=>'South Georgia And South Sandwich Islands',
'KR'=>'South Korea',
'ES'=>'Spain',
'LK'=>'Sri Lanka',
'SD'=>'Sudan',
'SR'=>'Suriname',
'SJ'=>'Svalbard And Jan Mayen',
'SZ'=>'Swaziland',
'SE'=>'Sweden',
'CH'=>'Switzerland',
'SY'=>'Syria',
'TW'=>'Taiwan',
'TJ'=>'Tajikistan',
'TZ'=>'Tanzania',
'TH'=>'Thailand',
'TG'=>'Togo',
'TK'=>'Tokelau',
'TO'=>'Tonga',
'TT'=>'Trinidad And Tobago',
'TN'=>'Tunisia',
'TR'=>'Turkey',
'TM'=>'Turkmenistan',
'TC'=>'Turks And Caicos Islands',
'TV'=>'Tuvalu',
'UG'=>'Uganda',
'UA'=>'Ukraine',
'AE'=>'United Arab Emirates',
'UK'=>'United Kingdom',
'US'=>'United States',
'UM'=>'United States Minor Outlying Islands',
'UY'=>'Uruguay',
'UZ'=>'Uzbekistan',
'VU'=>'Vanuatu',
'VA'=>'Vatican City (Holy See)',
'VE'=>'Venezuela',
'VN'=>'Vietnam',
'VG'=>'Virgin Islands (British)',
'VI'=>'Virgin Islands (US)',
'WF'=>'Wallis And Futuna Islands',
'EH'=>'Western Sahara',
'WS'=>'Western Samoa',
'YE'=>'Yemen',
'YU'=>'Yugoslavia',
'ZM'=>'Zambia',
'ZW'=>'Zimbabwe'
);
}

}	