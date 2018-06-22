<?php
namespace app\index\controller;
use think\Db;
class Address	 extends Home
{
  		//地址删除
  		public function del(){
  				($id = input('id')) || err('id is empty');
  				Db::name('address')->where('id',$id)->where('uid',UID)->delete()?succ():err('删除失败');
  				
  		}
  		public function set_detault(){
  				($id = input('id')) || err('id is empty');
  				$is_default = input('is_detault');
  				
  				if($is_default==1){
  						Db::name('address')->where('uid',UID)->setField('is_default',0);
  				}
  				Db::name('address')->where('id',$id)->where('uid',UID)->setField('is_default',$is_default)!==false?succ():err('设置失败');
  				
  				
  		}
  		
}
