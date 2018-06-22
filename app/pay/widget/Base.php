<?php
namespace app\index\widget;
use think\Controller;

class Base extends Controller
{	
		public function head(){
				//查出栏目
				$nav = db('nav')->order('sort')->group('pname')->select();
				
				foreach($nav as $v){
						$pname[] = $v['pname'];
				}
				$lists = db('nav')->select();
				$key_1 = [];
				foreach($lists as $v){
						
						if($key = array_search($v['pname'],$pname)){
									if(!isset($key_1[$key])){
										$nav[$key]['id'] = $v['id'];
										$key_1[$key] = 1;
									}
									if($v['pname']!==$v['name']){
											$nav[$key]['_child'][] = $v;
									}
						}
				}
				
				$this->assign('nav',$nav);
				
				return $this->fetch('base/head');
		}
	 	public function left(){
	 		
				 return $this->fetch('base/left');
	 	}
	 	public function footer(){
	 			return $this->fetch('base/footer');
	 	}
	 	public function list_to_tree($id){
				$groups = db('nav')->order('sort')->select();
				$current= '';
				$list = [];
				foreach($groups as $v){
					$key = $v['pname']?$v['pname']:$v['name'];
					($v['id']==$id)&&$current = $key;
					$list[$key][] = $v;
				}
				$this->assign('id',$id);
				$this->assign('current',$current);
				return $list;
		}
	 	
}
?>