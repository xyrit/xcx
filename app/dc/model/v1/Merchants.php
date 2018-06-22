<?php
namespace app\dc\model\v1;
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

			$having ='( distance < 1000)';
			if($admin_id){
				$mid = db('dc_appid')->where('admin_id',$admin_id)->value('uid');
				$ids = $this->query('select getchild('.$mid.') as ids');
				// dump($mid);
				add_log($ids[0]["ids"]);
				$ids = explode(',',$ids[0]["ids"]);
			}
			$where = $admin_id&&$ids?' uid in ('.implode($ids,',').') and ':'';
			$sql = 'SELECT uid as id, id as uid,base_url,city,county,address,merchant_name,logo_url,lon,lat,shipping_range,industry,shipping_type,slc(lat,lon,'.$lat.','.$lon.') as distance  FROM  ypt_merchants where '.$where.' end_time> '.time().' and is_open=1 and is_miniapp=2 and mini_type = 2 having('.$having.')  order by distance';
			add_log($sql);
			$data = $this->query($sql);
			// echo $this->getLastSql();
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
					$set = db('merchants_dc_set')->where('mid',$v['uid'])->field('serve_mode,work,start_time,end_time,is_serve,star,ps_price,qs_price,is_wm,id')->find();
					// dump(json_decode(htmlspecialchars_decode($set['ps_price']), true));
					// dump($set);dump($v['uid']);
					if ($set['ps_price']!='0.00') {
						// dump($set['id'].'11');
						$ps_price = json_decode(htmlspecialchars_decode($set['ps_price']), true);
						$set['ps_price'] = '';
						// dump($ps_price);dump($v['distance']);
						foreach ($ps_price as $key => $value) {
							// dump((float)$value['startDelivery']);echo "------------------<br>";dump(round($v['distance'] ,3));echo "------------------<br>";dump((float)$value['endDelivery']);
							if (round($v['distance'] ,3)>=(float)$value['startDelivery'] && round($v['distance'] ,2)<(float)$value['endDelivery']) {
								// echo "2222222";
								$set['ps_price'] = sprintf("%.2f", $value['ps_price']);
							}
							// dump($value);
						}
						if ($set['ps_price'] == '') {
							$set['ps_price'] = $ps_price[0]['ps_price'];
						}
					}else{
						$set['ps_price']='0.00';
					}
					if($v['distance']<1){
						$v['distance'] = (int)($v['distance']*1000).'m';
					}else{
						$v['distance'] = round($v['distance'] ,2).'km';
					}
					$v['base_url'] = $v['base_url']?URL.$v['base_url']:URL.'public/images/default.png';
					$v['logo_url'] = $v['logo_url']?$v['logo_url']:URL.'public/images/default.png';
					$v['address'] = $v['city'].$v['county'].$v['address'];
					
			        $coupons =   db('screen_coupons')->where('mid',$v['uid'])->where('status',3)->where('is_miniapp',2)->where(time() . '>= begin_timestamp and '. time() . ' <= end_timestamp')->where('quantity > 0')->field('status,is_miniapp,begin_timestamp,end_timestamp,quantity,up_price,de_price,total_price,title,card_id')->select();
			        $v['set'] = $set;
			        $v['coupons'] = $coupons;
			}
			// dump($data);
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
     * 判断是否可以购买
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
					return $this->getDistance($lat,$lon,$info['lat'],$info['lon']);
					// return ($info['shipping_range']>$this->getDistance($lat,$lon,$info['lat'],$info['lon']))?1:3;
			}
			// else{
			// 		//判断是否是全国年
			// 		$all_area[] = $area_id;
			// 		$area = db('area')->where('id',$area_id)->field('id,pid')->find();
					
			// 		if($area['pid']!=0){
			// 			$all_area[] = $area['pid'];
			// 			$area = db('area')->where('id',$area['pid'])->field('id,pid')->find();
			// 			if($area['pid']!=0){
			// 					$all_area[] = $area['pid'];
			// 					$area = db('area')->where('id',$area['pid'])->field('id,pid')->find();
			// 			}
			// 		}
			// 		$area = [];
			// 		$len = count($all_area);
			// 		for($i=0;$i<3;$i++){
			// 		$area[] =$len-->0?$all_area[$len]:0;
			// 		}
					
			// 		//查找附近店铺
			// 		//$this->query('select * from shipping_area where province_id = '.$area[0].' and (city_id = '')
			// 		//$area = db('shipping_area')->where('uid',$info['uid'])->select();
					
			// 		$data = db::name('shipping_area')->where('province_id','in',[0,$area[0]])->where('city_id','in',[0,$area[1]])->where('county_id','in',[0,$area[2]])->find();
			// 		add_log(db::name('shipping_area')->getLastSql());
			// 		return $data?1:3;
			// }
   }
 	  /**
     * 店铺信息
     */
    public function info($id){
    	$data = $this->where('uid',$id)->find();
    	$data->user_phone = db('merchants_users')->where('id',$data['uid'])->value('user_phone');
    	return $data;
    }

	/**
	 * 判断是否可以购买 2未开业 3距离不够 4不在营业时间内  5未开启外卖
	 */
	public function check_set($store_id,$lon,$lat,$area_id,$type)
	{
		$info = $this->where('uid',$store_id)->find();
		if($info['is_open']!=1){
			return array('status'=>2);
		}
		$set = db('merchants_dc_set')->where('mid',$info['id'])->field('work,start_time,end_time,ps_price,qs_price,is_wm')->find();
		if($set['work']!=1){
			return array('status'=>2);
		}
		$time = date('H:i:s',time());
		if ($time < $set['start_time']||$time>$set['end_time']) {
			return array('status'=>4);
		}
		add_log(json_encode($info));
		if ($type==1) {
			return array('status'=>1);
		}elseif($type==2){
			if($set['is_wm']!=1){
				return array('status'=>5);
			}
			if($info['shipping_type']==1){
				//获得距离
				add_log($this->getDistance($lat,$lon,$info['lat'],$info['lon']));
				$distance =  $this->getDistance($lat,$lon,$info['lat'],$info['lon']);
				
				if ($set['ps_price']!='0.00') {
					$ps_price = json_decode(htmlspecialchars_decode($set['ps_price']), true);
					$set['ps_price'] = '';
					foreach ($ps_price as $key => $value) {
						if ($distance/1000>=(float)$value['startDelivery'] && $distance/1000<(float)$value['endDelivery']) {
							$set['ps_price'] = sprintf("%.2f", $value['ps_price']);
							return array('status'=>1,'ps_price'=>$set['ps_price']);
						}
					}
					if ($set['ps_price'] == '') {
						return array('status'=>3);
					}
				}else{
					return array('status'=>1,'ps_price'=>'0.00');
				}
			}else{
				return array('status'=>1,'ps_price'=>'0.00');
			}
		}
		
   }
}