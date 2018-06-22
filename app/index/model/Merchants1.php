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
	    public function lists(){
	    			
	    }  
	    /**
	     * 根据定位返回店铺列表
	     */
	    public function distance_store($lon,$lat,$distance=1000,$area_id,$admin_id){
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
				//add_log($sql);
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
			     $earthRadius = 6367; //approximate radius of earth in meters  
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
						add_log($this->getDistance($lat,$lon,$info['lat'],$info['lon']));
						
						return ($info['shipping_range']>$this->getDistance($lat,$lon,$info['lat'],$info['lon']))?1:3;
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
	    	return $data;
	    }
}