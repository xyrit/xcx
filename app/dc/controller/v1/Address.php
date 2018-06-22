<?php
namespace app\dc\controller\v1;
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
  				if(Db::name('address')->where('id',$id)->where('uid',UID)->setField('is_default',$is_default)){
            $this->address();
          }else{
            err('设置失败');
          }
  				
  				
  		}

      /**
       * 默认地址
       */
      public function _default(){
          $data = Db::name('address')->where(['uid'=>UID,'is_default'=>1])->find();
          if ($data) {
            succ($data);
          }else{
            err();
          }
      }

      public function address(){
        ($mid = input('store_id')) || err('store_id is empty');
        $user = model('user');
        $lists = $user->address();
        $dcset = model('dcset');
        $dcset = $dcset->lists($mid);
        // dump($lists);dump($dcset);
        // $lists = array_merge($lists,$dcset['ps_price']);
        succ($lists,$dcset['ps_price']);
      }

      public function address_update(){
          
          $data = input();
          if ($data['name1']==false) {
            err('收货人不能为空');
          }
          $data['name'] = $data['name1'];
          unset($data['name1']);
          unset($data['token']);
          unset($data['version']);
          unset($data['action']);
          $data['uid'] = UID;
          $address = model('address');
          if($address->_update($data)!==false){
            succ();
          }else{
            err($address->getError());
          }
      }

      public function address_info(){
          ($id = input('id')) || err('id is empty');
          succ(model('address')->info($id));
      }
  		
}
