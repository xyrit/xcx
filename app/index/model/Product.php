<?php
namespace app\index\model;
use think\Model;
use think\Db;
class Product extends Model
{
	public $name = 'goods';	
	public function lists($uid=0,$group_id=0)
	{
		if ($group_id==0) {
			$gid=0;
		}else{
			//判断几级分组
			$goods_group = db::name('goods_group')->where('group_id',$group_id)->find();
			if($goods_group['gid']==0){
				//一级分组
				$gid = db::name('goods_group')->where('gid',$group_id)->select();
			}else{
				//二级分组
				$gid=0;
			}
		}
		if ($gid) {
			$id = array();
			foreach ($gid as $key => $value) {
				$id = array_merge($id,array($key=>$value['group_id'],$group_id));
			}
			$id = join(',',$id);
			$where['group_id'] = array('in',$id);
			$where['mid']=$uid;
			$where['is_delete'] = 0;
			$where['put_xcx'] = 2;
			$where['trade'] = 1;
			$lists = $this->where($where)->field('goods_id,goods_name,shop_price as price,pj_nums,pj_rate,goods_brief,star,sales,original_price,group_id,goods_img1')->paginate(10);
			// echo $this->getLastSql();
			foreach($lists as &$v){
				$picture = $v['goods_img1'];
				if(preg_match("/\x20*https?\:\/\/.*/i",$v['goods_img1'])){
				    $v['picture'] = $picture;
				}else{
				    $v['picture'] = URL.$picture;
				}
			}
			return $lists;
		}else{
			$where['group_id'] = $group_id;
			$where['mid']=$uid;
			$where['is_delete'] = 0;
			$where['put_xcx'] = 2;
			$where['trade'] = 1;
			$lists = $this->where($where)->field('goods_id,goods_name,shop_price as price,pj_nums,pj_rate,goods_brief,star,sales,original_price,group_id,goods_img1')->paginate(10);
			// echo $this->getLastSql();
			// dump($lists);
			foreach($lists as &$v){
				$picture = $v['goods_img1'];
				if(preg_match("/\x20*https?\:\/\/.*/i",$v['goods_img1'])){
				    $v['picture'] = $picture;
				}else{ 
				    $v['picture'] = URL.$picture;
				}
			}
			return $lists;
		}
		
	}
	//热门商品
	public function hot($uid,$limit = 2)
	{
		$where['mid']=$uid;
		$where['is_delete']=0;
		$where['put_xcx']=2;
		$where['trade']=1;
		$lists =  $this->where($where)->field('goods_id,goods_name,shop_price as price,pj_nums,pj_rate,goods_img1')->order('sales desc')->limit(2)->select();
		foreach($lists as &$v){
				$picture = $v['goods_img1'];
				if(preg_match("/\x20*https?\:\/\/.*/i",$v['goods_img1'])){
				    $v['picture'] = $picture;
				}else{ 
				    $v['picture'] = URL.$picture;
				}
		}
		return $lists;
	}
	//商品信息
	public function liteinfo($goods_id,$field=true){
			if(!$data = $this->field($field)->find($goods_id)){
				return $this->error('不存在商品');
			}
			return $data;
	}
	//商品信息
	public function info($goods_id,$field=true){
			$goods_id || $this->error('goods_id is empty');
			$data = $this->field($field)->find($goods_id)->toArray();
			// $product_attach = db::name('product_attach')->where('goods_id',$goods_id)->find();
			$goods_desc = db::name('goods_desc_img')->where('goods_id',$goods_id)->select();
			// dump($data);
			for($i=1,$len=8;$i<$len;$i++){
					// $data['goods_img'.$i]&&$data['pictures'][] = URL.$data['goods_img'.$i];
					// $product_attach['goods_img'.$i]&&$data['pictures'][] = URL.$product_attach['goods_img'.$i];

					if($data['goods_img'.$i]){
						$picture[$i] = $data['goods_img'.$i];
						if(preg_match("/\x20*https?\:\/\/.*/i",$data['goods_img'.$i])){
						    $data['pictures'][] = $picture[$i];
						}else{ 
						    $data['pictures'][] = URL.$picture[$i];
						}
					}
			}
			//查看规格
			$data['property'] = db::name('goods_sku')->where('goods_id',$goods_id)->select();
			foreach ($data['property'] as $k => &$v) {
				if ($v['properties']=='0') {
                        $v['properties'] = db::name('units')->where(array('id'=>$v['units_id']))->value('unit_name');
                    }
			}
			$data['desc'] = [];
			// for($i=1,$len=4;$i<$len;$i++){
			// 		if($data['pic_desc'.$i]){
			// 				$data['desc'][] = ['url'=>URL.$data['pic_desc'.$i],'text'=>$data['goods_desc'.$i]];
			// 		}
			// }
			if($goods_desc){
				foreach ($goods_desc as $key => $value) {
					$url = $value['url'];
						if(preg_match("/\x20*https?\:\/\/.*/i",$value['url'])){
						    $data['desc'][] = ['url'=>$url];
						}else{ 
						    $data['desc'][] = ['url'=>URL.$url];
						}
				}
			}
			
			return $data;
	}
	//库存是否 
	public function check_good($goods_id,$attr=0,$nums){
			if(!$data = $this->where('goods_id',$goods_id)->field('goods_name,goods_number,shop_price,mid,goods_img1')->find()){
					return $this->error('不存在该商品');
			}
			// $product_attach = db::name('product_attach')->where('goods_id',$goods_id)->find();
			$picture = $data['goods_img1'];
			if(preg_match("/\x20*https?\:\/\/.*/i",$data['goods_img1'])){
			    $data['goods_img1'] = $picture;
			}else{
			    $data['goods_img1'] = URL.$picture;
			}
			if($attr==0){
				$sku = $data['goods_number'];
			}else{
				$sku = db::name('goods_sku')->where('sku_id',$attr)->where('goods_id',$goods_id)->value('quantity');
			}
			if($nums>$sku){
				return $this->error('库存不足');
			}else{
				return $data;
			}
	}
	//1:推荐 2:新品 3:热卖
	public function get_tag($tag_id){
			return $this->where("tag & {$tag_id} = {$tag_id}")->where('state',1)->select();;
	}
	
	public function error($msg){
			$this->error = $msg;
			return false;
	}

	/**
	 * 关键词模糊搜索
	 */
	public function search_lists($search_name,$uid)
	{
		$where['is_on_sale'] = 1;
		$where['is_delete'] = 0;
		$where['put_xcx'] = 2;
		$where['mid'] = $uid;
		$where['trade']=1;
		$where['goods_name'] = array('like','%'.$search_name.'%');
		$lists = $this->where($where)->field('goods_id,goods_name,shop_price as price,pj_nums,pj_rate,goods_brief,star,sales,original_price,goods_img1')->order('goods_id desc')->paginate(10);
		// echo $this->getLastSql();
		foreach($lists as &$v){
			// $product_attach = db::name('product_attach')->where('goods_id',$v['goods_id'])->find();
			$picture = $v['goods_img1'];
			if(preg_match("/\x20*https?\:\/\/.*/i",$v['goods_img1'])){
			    $v['picture'] = $picture;
			}else{
			    $v['picture'] = URL.$picture;
			}
		}
		 // dump($this->getLastSql());die;
		return $lists;
	}

	/**
	 * 新品上市
	 * @param  integer $uid [description]
	 * @return [type]       [description]
	 */
	public function news($uid=0){
			$where['mid']=$uid;
			$where['is_on_sale'] = 1;
			$where['is_delete'] = 0;
			$where['put_xcx'] = 2;
			$where['trade']=1;
			$lists = $this->where($where)->field('goods_id,goods_name,shop_price as price,pj_nums,pj_rate,goods_brief,star,sales,original_price,goods_img1')->order('goods_id desc')->paginate(10);
			foreach($lists as &$v){
				// $product_attach = db::name('product_attach')->where('goods_id',$v['goods_id'])->find();
				$picture = $v['goods_img1'];
				if(preg_match("/\x20*https?\:\/\/.*/i",$v['goods_img1'])){
				    $v['picture'] = $picture;
				}else{
				    $v['picture'] = URL.$picture;
				}
			}
			return $lists;
	}

	/**
	 * 热门商品
	 * @param  integer $uid [description]
	 * @return [type]       [description]
	 */
	public function sell_hot($uid=0)
	{
		$where['mid']=$uid;
		$where['is_on_sale'] = 1;
		$where['is_delete'] = 0;
		$where['put_xcx'] = 2;
		$where['is_hot'] = 1;
		$where['trade']=1;
		$lists = $this->where($where)->field('goods_id,goods_name,shop_price as price,pj_nums,pj_rate,goods_brief,star,sales,original_price,goods_img1')->order(array('hot_sort'=>'DESC','goods_id' => 'DESC'))->paginate(10);
		foreach($lists as &$v){
			// $product_attach = db::name('product_attach')->where('goods_id',$v['goods_id'])->field('goods_img1')->find();
			$picture = $v['goods_img1'];
			if(preg_match("/\x20*https?\:\/\/.*/i",$v['goods_img1'])){
			    $v['picture'] = $picture;
			}else{ 
			    $v['picture'] = URL.$picture;
			}
		}
		return $lists;
	}

	public function classify($uid=0,$group_id=0){
			if ($group_id==0) {
				$gid=0;
			}else{
				//判断几级分组
				$goods_group = db::name('goods_group')->where('group_id',$group_id)->find();
				if($goods_group['gid']==0){
					//一级分组  有二级分组
					$gid = db::name('goods_group')->where('gid',$group_id)->order('sort')->find();
				}else{
					//二级分组
					$gid=0;
				}
			}
			if ($gid) {
				
				$where['group_id'] = $gid['group_id'];
				$where['mid']=$uid;
				$where['is_delete'] = 0;
				$where['put_xcx'] = 2;
				$where['trade']=1;
				$lists = $this->where($where)->field('goods_id,goods_name,shop_price as price,pj_nums,pj_rate,goods_brief,star,sales,original_price,group_id,goods_img1')->paginate(10);
				// echo $this->getLastSql();
				foreach($lists as &$v){
					// $product_attach = db::name('product_attach')->where('goods_id',$v['goods_id'])->find();
					$picture = $v['goods_img1'];
					if(preg_match("/\x20*https?\:\/\/.*/i",$v['goods_img1'])){
					    $v['picture'] = $picture;
					}else{
					    $v['picture'] = URL.$picture;
					}
				}
				return $lists;
			}else{
				$where['group_id'] = $group_id;
				$where['mid']=$uid;
				$where['is_delete'] = 0;
				$where['put_xcx'] = 2;
				$where['trade']=1;
				$lists = $this->where($where)->field('goods_id,goods_name,shop_price as price,pj_nums,pj_rate,goods_brief,star,sales,original_price,group_id,goods_img1')->paginate(10);
				// echo $this->getLastSql();
				foreach($lists as &$v){
					// $product_attach = db::name('product_attach')->where('goods_id',$v['goods_id'])->find();
					$picture = $v['goods_img1'];
					if(preg_match("/\x20*https?\:\/\/.*/i",$v['goods_img1'])){
					    $v['picture'] = $picture;
					}else{
					    $v['picture'] = URL.$picture;
					}
				}
				return $lists;
			}
		}

		public function classGoods($uid=0,$group_id=0){
			if ($group_id==0) {
				$gid=0;
			}else{
				//判断几级分组
				$goods_group = db::name('goods_group')->where('group_id',$group_id)->find();
				if($goods_group['gid']==0){
					//一级分组  有二级分组
					$gid = db::name('goods_group')->where('gid',$group_id)->order('sort')->find();
				}else{
					//二级分组
					$gid=0;
				}
			}
			if ($gid) {
				
				$where['group_id'] = $gid['group_id'];
				$where['mid']=$uid;
				$where['is_delete'] = 0;
				$where['put_xcx'] = 2;
				$where['trade']=1;
				$lists = $this->where($where)->field('goods_id,goods_name,shop_price as price,pj_nums,pj_rate,goods_brief,star,sales,original_price,group_id,goods_img1')->paginate(10);
				// echo $this->getLastSql();
				foreach($lists as &$v){
					// $product_attach = db::name('product_attach')->where('goods_id',$v['goods_id'])->find();
					$picture = $v['goods_img1'];
					if(preg_match("/\x20*https?\:\/\/.*/i",$v['goods_img1'])){
					    $v['picture'] = $picture;
					}else{
					    $v['picture'] = URL.$picture;
					}
				}
				return $lists;
			}else{
				$where['group_id'] = $group_id;
				$where['mid']=$uid;
				$where['is_delete'] = 0;
				$where['put_xcx'] = 2;
				$where['trade']=1;
				$lists = $this->where($where)->field('goods_id,goods_name,shop_price as price,pj_nums,pj_rate,goods_brief,star,sales,original_price,group_id,goods_img1')->paginate(10);
				// echo $this->getLastSql();
				foreach($lists as &$v){
					// $product_attach = db::name('product_attach')->where('goods_id',$v['goods_id'])->find();
					$picture = $v['goods_img1'];
					if(preg_match("/\x20*https?\:\/\/.*/i",$v['goods_img1'])){
					    $v['picture'] = $picture;
					}else{
					    $v['picture'] = URL.$picture;
					}
				}
				return $lists;
			}
		}
}