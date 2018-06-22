<?php
namespace app\index\model;
use think\Model;
use think\Session;
use think\Db;
class Merchants extends Model
{
		
		protected $createTime =  'add_time';
		protected $updateTime = 'update_time';
		protected $auto  = ['state' => 1];
	 	/*
	 	 * 店铺列表
	 	 */
	    public function lists($lon,$lat,$distance=1000,$area_id,$admin_id,$page=0,$trade,$search_name)
	    {
	    	 $data1 = db::name('merchants')
	    	->alias('m')
            ->join('ypt_miniapp mi','mi.mid=m.uid')
            ->where(array('mi.type'=>1,'mi.start_time'=>array('elt',time()),'mi.end_time'=>array('egt',time()),'m.is_miniapp'=>2,'m.status'=>1,'mi.is_enter'=>1))
            ->whereOr(array('mi.type'=>1,'m.is_miniapp'=>2,'m.status'=>1,'mi.is_enter'=>1,'mi.is_time'=>0))
            ->field('mi.mid,mi.type')
            ->select();
            // echo db::name('merchants')->getLastSql();
            $data2 = array();
            switch ($trade) {
            	case '零售行业':
            		$trade =1;
            		break;
            	case '餐饮行业':
            		$trade =2;
            		break;
            	case '其他行业':
            		return array('data'=>array(),'total'=>0);
            		break;
            	default:
            		$trade =0;
            		break;
            }
            foreach($data1 as $v){
            	if ($trade) {
            		if ($trade==$v['type']) {
	            		$data2[] = $v['mid'];
	            	}
            	}else{
            		$data2[] = $v['mid'];
            	}
            	
				
			}
			//只显示代理商的店铺
			$ids= '';
			if($admin_id){
				$mid = db('appid')->where('uid',$admin_id)->value('mid');
				$ids = $this->query('select getagentchild('.$mid.') as ids');
				add_log($ids[0]["ids"]);
				$ids = explode(',',$ids[0]["ids"]);
				foreach($data2 as $key=>$v){
					if(!in_array($v,$ids)){
						unset($data2[$key]);
					}
				}
			}
			
   			$per_page = 20;
			$count = $per_page*$page;
			$where1 = $admin_id&&$admin_id!=888&&$ids?' uid in ('.implode($ids,',').') and ':'';
			// $trade = $trade?' and trade ='.$trade;
			$search_name = $search_name?" and merchant_name like '%".$search_name."%'":'';
			$having =$data2?'uid in ('.implode($data2,',').')':'0';
			$sql = 'SELECT uid as id,base_url,city,county,address,merchant_name,logo_url,lon,lat,shipping_range,industry,shipping_type,slc(lat,lon,'.$lat.','.$lon.') as distance  FROM  ypt_merchants where '.$where1.' end_time> '.time().' and is_open=1 '.$search_name.' and is_miniapp=2 having('.$having.') order by distance limit '.$count.','.$per_page;
			$c = 'SELECT uid as id,base_url,city,county,address,merchant_name,logo_url,lon,lat,shipping_range,industry,shipping_type,slc(lat,lon,'.$lat.','.$lon.') as distance  FROM  ypt_merchants where '.$where1.' end_time> '.time().' and is_open=1 '.$search_name.' and is_miniapp=2  having('.$having.') order by distance';
			$total = $this->query($c);
			$total = count($total);
			$data = $this->query($sql);	
			// echo $this->getLastSql();die;
			$i = $j = $a = $b = '-';
			foreach($data as $k=> &$v){
				$v['trade'] = db::name('miniapp')->where(array('mid'=>$v['id']))->order('id desc')->value('type');
					switch($v['industry']){
							case '手机行业':
							$v['path'] = '/images/icon/mobile.png';
							break;
							case '餐饮行业':
							$v['path'] = '/images/icon/cy.png';
							break;
							case '零售超市':
							$v['path'] = '/images/icon/cs.png';
							break;
							case '服装鞋帽':
							$v['path'] = '/images/icon/fz.png';
							break;
							case '美容美发':
							$v['path']  = '/images/icon/mr.png';
							break;
							default:
							$v['path'] = '/images/icon/default.png';
							break;
					}
					if($v['distance']<1){
						$v['distance'] = (int)($v['distance']*1000).'m';
					}else{
						$v['distance'] = round($v['distance'] ,2).'km';
					}
					$v['base_url'] = $v['base_url']?URL.$v['base_url']:URL.'public/images/default.png';
					$v['address'] = $v['city'].$v['county'].$v['address'];
					if ($v['id']==3393) {
						$i= $k;
					}
					if ($v['id']==3619) {
						$j= $k;
					}
					if ($v['id']==724) {
						$a= $k;
					}
					if ($v['id']==15) {
						$b= $k;
					}
			}
			// dump($data);dump($a);
			// if ($admin_id==66) {
			// 	if ($i!=='-') {
			// 		$arr = $data[$i];
			// 		unset($data[$i]);
			// 	}
			// 	if ($j!=='-') {
			// 		$arr2 = $data[$j];
			// 		unset($data[$j]);
			// 	}
			// 	array_unshift($data,$arr2,$arr);
			// }
			if ($admin_id==24) {
				if($a!=='-'){
					// echo "string";
					unset($data[$a]);
					$total--;
				}
				if ($b!=='-') {
					$arr = $data[$b];
					unset($data[$b]);
					array_unshift($data,$arr);
				}
				$data=array_merge($data);
			}
   			return array('data'=>$data,'total'=>$total);	
	    }

	    /**
	     * 根据定位返回店铺列表
	     */
	    public function distance_store($lon,$lat,$distance=1000,$area_id,$admin_id,$page=0)
	    {
	    	 $data1 = db::name('merchants')
	    	->alias('m')
            ->join('ypt_miniapp mi','mi.mid=m.uid')
            ->where(array('mi.type'=>1,'mi.start_time'=>array('elt',time()),'mi.end_time'=>array('egt',time())))
            ->field('mi.mid,mi.type')
            ->select();
            // echo db::name('merchants')->getLastSql();
            foreach($data1 as $v){
				$data2[] = $v['mid'];
			}
			//只显示代理商的店铺
			if($admin_id==158){
				$sql = 'SELECT uid as id,base_url,city,county,address,merchant_name,logo_url,lon,lat,shipping_range,industry,shipping_type,slc(lat,lon,'.$lat.','.$lon.') as distance  FROM  ypt_merchants where id = 158';
			}else{
			if($admin_id!=888){
				if($admin_id){
					$mid = db('appid')->where('uid',$admin_id)->value('mid');
					$ids = $this->query('select getagentchild('.$mid.') as ids');
					add_log($ids[0]["ids"]);
					$ids = explode(',',$ids[0]["ids"]);
					foreach($data2 as $key=>$v){
						if(!in_array($v,$ids)){
							unset($data2[$key]);
						}
					}
				}
			}
			$per_page = 10;
			$count = $per_page*$page;
			$where1 = $admin_id&&$admin_id!=888&&$ids?' uid in ('.implode($ids,',').') and ':'';
			$having =$data2?'uid in ('.implode($data2,',').')':'0';
			$sql = 'SELECT uid as id,base_url,city,county,address,merchant_name,logo_url,lon,lat,shipping_range,industry,shipping_type,slc(lat,lon,'.$lat.','.$lon.') as distance  FROM  ypt_merchants where '.$where1.' end_time> '.time().' and is_open=1 and is_miniapp=2 having('.$having.') order by distance';
			}
			// dump($sql);
			$data = $this->query($sql);
		//	add_log($this->getLastSql());
//				\think\Debug::remark('end');	 
//				add_log(\think\Debug::getRangeTime('begin','end').'s');		
			foreach($data as $k=> &$v){
					switch($v['industry']){
							case '手机行业':
							$v['path'] = '/images/icon/mobile.png';
							break;
							case '餐饮行业':
							$v['path'] = '/images/icon/cy.png';
							break;
							case '零售超市':
							$v['path'] = '/images/icon/cs.png';
							break;
							case '服装鞋帽':
							$v['path'] = '/images/icon/fz.png';
							break;
							case '美容美发':
							$v['path']  = '/images/icon/mr.png';
							break;
							default:
							$v['path'] = '/images/icon/default.png';
							break;
					}
					if($v['distance']<1){
						$v['distance'] = (int)($v['distance']*1000).'m';
					}else{
						$v['distance'] = round($v['distance'] ,2).'km';
					}
					$v['base_url'] = $v['base_url']?URL.$v['base_url']:URL.'public/images/default.png';
					$v['address'] = $v['city'].$v['county'].$v['address'];
					if ($v['id']==3393) {
						$i= $k;
					}
					if ($v['id']==3619) {
						$j= $k;
					}
					if ($v['id']==724) {
						$a= $k;
					}
					if ($v['id']==15) {
						$b= $k;
					}
			}
			if ($admin_id==66) {
				$arr = $data[$i];
				$arr2 = $data[$j];
				unset($data[$i]);
				unset($data[$j]);
				array_unshift($data,$arr2,$arr);
			}
			if ($admin_id==24) {
				unset($data[$a]);
				$arr = $data[$b];
				unset($data[$b]);
				array_unshift($data,$arr);
				$data=array_merge($data);
			}
   			return $data;
	    }

	    /**
	     * 根据定位返回店铺列表
	     */
	    public function distance_store3($lon,$lat,$distance=1000,$area_id,$admin_id){
//	    		\think\Debug::remark('begin');
			
	    				$all_area[] = $area_id;
	    				
						$area = db('area')->where('id',$area_id)->field('id,pid')->find();
						if($area['pid']!=0){
							$all_area[] = $area['pid'];
							$area = db('area')->where('id',$area['pid'])->field('id,pid')->find();
							if($area['pid']!=0){
									$all_area[] = $area['pid'];
									$area = db('area')->where('id',$area['pid'])->field('id,pid')->find();
							}
						}
						$area = [];
						$len = count($all_area);
						for($i=0;$i<3;$i++){
							$area[] =$len-->0?$all_area[$len]:0;
						}
						
				$url = 'select b.uid as id  from (select uid  from ypt_shipping_area where province_id in ('.$area[0].',0) and city_id in ('.$area[1].',0) and  county_id in ('.$area[2].',0)) as a left join ypt_merchants b on a.uid = b.uid where b.is_open=1 and b.shipping_type=2 and b.end_time>'.time();
				//add_log($url);
				$data1 = $this->query($url);
				foreach($data1 as $v){
						$data2[] = $v['id'];
				}
				//add_log(implode($data2,','));
				//只显示代理商的店铺
				if($admin_id==158){
					$sql = 'SELECT uid as id,base_url,city,county,address,merchant_name,logo_url,lon,lat,shipping_range,industry,shipping_type,slc(lat,lon,'.$lat.','.$lon.') as distance  FROM  ypt_merchants where id = 158';
				}else{
				if($admin_id!=888){
					if($admin_id){
						$mid = db('appid')->where('uid',$admin_id)->value('mid');
						//$ids = db('merchants_users')->where('pid',$mid)->column('id');
						
						
						$ids = $this->query('select getchild('.$mid.') as ids');
						add_log($ids[0]["ids"]);
						$ids = explode(',',$ids[0]["ids"]);
						
						//add_log('select getchild('.$mid.') as ids');
						
						//$data2  = array_intersect($ids,$data2);
						foreach($data2 as $key=>$v){
							if(!in_array($v,$ids)){
								unset($data2[$key]);
							}
						}
						//$data2 = array_unique(array_merge($ids,$data2));
					}
				}
					$where1 = $admin_id&&$admin_id!=888&&$ids?' uid in ('.implode($ids,',').') and ':'';
				
				$where = $data2&&$data2?'(uid in ('.implode($data2,',').') and shipping_type = 2) or ':'';
				
				$having = $where.'(shipping_type=1 and distance < shipping_range)';
					
				$sql = 'SELECT uid as id,base_url,city,county,address,merchant_name,logo_url,lon,lat,shipping_range,industry,shipping_type,slc(lat,lon,'.$lat.','.$lon.') as distance  FROM  ypt_merchants where '.$where1.' end_time> '.time().' and is_open=1 having('.$having.')  order by distance';
				}
				// dump($sql);
				$data = $this->query($sql);
			//	add_log($this->getLastSql());
//				\think\Debug::remark('end');	 
//				add_log(\think\Debug::getRangeTime('begin','end').'s');		
				foreach($data as &$v){
						switch($v['industry']){
								case '手机行业':
								$v['path'] = '/images/icon/mobile.png';
								break;
								case '餐饮行业':
								$v['path'] = '/images/icon/cy.png';
								break;
								case '零售超市':
								$v['path'] = '/images/icon/cs.png';
								break;
								case '服装鞋帽':
								$v['path'] = '/images/icon/fz.png';
								break;
								case '美容美发':
								$v['path']  = '/images/icon/mr.png';
								break;
								default:
								$v['path'] = '/images/icon/default.png';
								break;
						}
						if($v['distance']<1){
							$v['distance'] = (int)($v['distance']*1000).'m';
						}else{
							$v['distance'] = round($v['distance'] ,2).'km';
						}
						$v['base_url'] = $v['base_url']?URL.$v['base_url']:URL.'public/images/default.png';
						$v['address'] = $v['city'].$v['county'].$v['address'];
				}
	   			return $data;
	    }
	    /**
	     * 获得用户和店铺的距离
	     */
		public function getDistance($lat1, $lng1, $lat2, $lng2)  
		{
			     $earthRadius = 6367000; //approximate radius of earth in meters  
			     $lat1 = ($lat1 * pi() ) / 180;  
			     $lng1 = ($lng1 * pi() ) / 180;  
			     $lat2 = ($lat2 * pi() ) / 180;  
			     $lng2 = ($lng2 * pi() ) / 180;  
			     $calcLongitude = $lng2 - $lng1;  
			     $calcLatitude = $lat2 - $lat1;  
			     $stepOne = pow(sin($calcLatitude / 2), 2) + cos($lat1) * cos($lat2) * pow(sin($calcLongitude / 2), 2);    
			     $stepTwo = 2 * asin(min(1, sqrt($stepOne)));  
			     $calculatedDistance = $earthRadius * $stepTwo; 
			     return round($calculatedDistance);
			     
		}
	    /**
	     * 判断是否可以购买 2未开业 3距离不够
	     */
	    public function check_store($store_id,$lon,$lat,$area_id){
	    		$info = $this->where('uid',$store_id)->find();
	    		if($info['is_open']!=1){
	    				return 2;
	    		}
	    		add_log(json_encode($info));
				if($info['shipping_type']==1){
						//获得距离
						add_log($this->getDistance($lat,$lon,$info['lat'],$info['lon'])/1000);
						return ($info['shipping_range']>$this->getDistance($lat,$lon,$info['lat'],$info['lon'])/1000)?1:3;
				}else{
						//判断是否是全国年
						$all_area[] = $area_id;
						$area = db('area')->where('id',$area_id)->field('id,pid')->find();
						
						if($area['pid']!=0){
							$all_area[] = $area['pid'];
							$area = db('area')->where('id',$area['pid'])->field('id,pid')->find();
							if($area['pid']!=0){
									$all_area[] = $area['pid'];
									$area = db('area')->where('id',$area['pid'])->field('id,pid')->find();
							}
						}
						$area = [];
						$len = count($all_area);
						for($i=0;$i<3;$i++){
						$area[] =$len-->0?$all_area[$len]:0;
						}
						
						//查找附近店铺
						//$this->query('select * from shipping_area where province_id = '.$area[0].' and (city_id = '')
						//$area = db('shipping_area')->where('uid',$info['uid'])->select();
						
						$data = db::name('shipping_area')->where('province_id','in',[0,$area[0]])->where('city_id','in',[0,$area[1]])->where('county_id','in',[0,$area[2]])->find();
						add_log(db::name('shipping_area')->getLastSql());
						return $data?1:3;
				}
	   }
	 	  /**
	     * 店铺信息
	     */
	    public function info($id){
	    	$data = $this->where('uid',$id)->find();
	    	$data->user_phone = db('merchants_users')->where('id',$data['uid'])->value('user_phone');
	    	if ($data->shipping_img) {
	    		$picture = $data->shipping_img;
				if(preg_match("/\x20*https?\:\/\/.*/i",$data->shipping_img)){
				    $data->shipping_img = $picture;
				}else{
				    $data->shipping_img = URL.$picture;
				}
	    	}
	    	
	    	$mid = $this->_get_mch_id($id);
	    	if(db('merchants_kefu')->where('mid',$mid)->value('qrcode')){
	    		$data->ke_wechat = 'https://sy.youngport.com.cn'.db('merchants_kefu')->where('mid',$mid)->value('qrcode');
	    	}
	    	
	    	return $data;
	    }

	    /**
	     * 获取商家ID
	     * @Param uid 商家uid
	     */
	    public function _get_mch_id($uid)
	    {
	        $id = db('merchants')->where(array('uid'=>$uid))->value('id');
	        return $id;
	    }

	    /**
	     * 判断是否可以购买 2未开业 3距离不够
	     */
	    public function check_set($store_id,$lon,$lat,$area_id){
    		$info = $this->where('uid',$store_id)->find();
    		if($info['is_open']!=1){
    				return 2;
    		}
    		add_log(json_encode($info));
			if($info['shipping_type']==1){
					
				//获得距离
				add_log($this->getDistance($lat,$lon,$info['lat'],$info['lon'])/1000);
				$distance = $this->getDistance($lat,$lon,$info['lat'],$info['lon']);
				$shipping = db::name('shipping_near')->where(array('uid' => $store_id))->select();
				foreach ($shipping as $key => $value) {
					($value['end_distance']>$this->getDistance($lat,$lon,$info['lat'],$info['lon'])/1000)? $status =1: $status=3;
					$shipping_qs = $value['shipping_qs'];
					// unset($value['shipping_qs']);
				}
				return array('status'=>$status,'shipping_type'=>$info['shipping_type'],'shipping'=>$shipping,'distance'=>$distance,'shipping_qs'=>$shipping_qs);
			}else{
				//判断是否是全国年
				$all_area[] = $area_id;
				$area = db('area')->where('id',$area_id)->field('id,pid')->find();
				
				if($area['pid']!=0){
					$all_area[] = $area['pid'];
					$area = db('area')->where('id',$area['pid'])->field('id,pid')->find();
					if($area['pid']!=0){
							$all_area[] = $area['pid'];
							$area = db('area')->where('id',$area['pid'])->field('id,pid')->find();
					}
				}
				$area = [];
				$len = count($all_area);
				for($i=0;$i<3;$i++){
				$area[] =$len-->0?$all_area[$len]:0;
				}
				
				//查找附近店铺
				//$this->query('select * from shipping_area where province_id = '.$area[0].' and (city_id = '')
				//$area = db('shipping_area')->where('uid',$info['uid'])->select();
				$data = db::name('shipping_area')->where('province_id','in',[0,$area[0]])->where('city_id','in',[0,$area[1]])->where('county_id','in',[0,$area[2]])->where('uid',$store_id)->find();
				// echo db::name('shipping_area')->getLastSql();
				add_log(db::name('shipping_area')->getLastSql());
				$data?$status=1:$status=3;
				$data['shipping_type']  = $info['shipping_type'];
				$data['shipping_qs']  = $info['shipping_qs'];
				$data['shipping_ps']  = $info['shipping_ps'];
				$data['shipping_free']  = $info['shipping_free'];
				$data['status']  = $status;
				return $data;
			}
	   }
}