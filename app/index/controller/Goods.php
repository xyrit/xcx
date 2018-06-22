<?php
namespace app\index\controller;
use think\Controller;
use think\Db;
use app\index\model\Goods as GoodsModel;

class Goods extends controller
{
    public function lists()
    {
    		($store_id = input('store_id')) || err('store_id is mepty');
    		$lists = model('goods')->lists($store_id,input('group_id'));
    		
			succ($lists);
    }
    //热门商品
    public function hot()
    {
            ($store_id = input('store_id')) || err('store_id is mepty');
            $lists = model('goods')->sell_hot($store_id);
            
            succ($lists);
    }
    public function info(){
    		($goods_id = input('id')) || err('id is empty');
    		$data = model('goods')->info($goods_id);
    		$data['user_phone'] = db('merchants_users')->where('id',$data['mid'])->value('user_phone');
    		$data['pj'] = model('pj')->news($goods_id);
    		succ($data);
    }
    public function pj(){
    		($goods_id = input('goods_id')) || err('goods_id is empty');
    		$lists = model('pj')->lists($goods_id);
    		succ($lists);
    }
    /*
     * 获取商品分类
     */
    public function category(){
    		($store_id  = input('store_id')) || err('store_id is empty');
    		$category = model('group');
    		$data = $category->lists($store_id);
    		succ($data);
    }
    public function class_lists(){
    			Db::name('goods_class')->where('status',1) -> select();
    }

    /**
     * 搜索商品
     */
    public function search_goods()
    {
        $search_name = input('search_name'); //搜索关键词
        ($store_id  = input('store_id')) || err('store_id is empty');   //商户uid
        $goods = model('product');
        $data = $goods->search_lists($search_name,$store_id);
        if ($data) {
            succ($data);
        }else{
            succ('商品为空');
        }
    }

    /**
     * 新品上市列表
     */
    public function news()
    {
        ($store_id = input('store_id')) || err('store_id is mepty');
        $lists = model('goods')->news($store_id);
        succ($lists);
    }

    //向代理商品库导入商品
    public function product()
    {
        $goods = GoodsModel::where(array('is_delete'=>0))->select();
        foreach ($goods as $key => $value) {
            $data = array(
                'goods_name'=>$value['goods_name'],
                'bar_code'=>$value['bar_code']?$value['bar_code']:0,
                'window_img'=>$value['goods_img1']?$value['goods_img1']:'',
                'buy_price'=>$value['buy_price']?$value['buy_price']:0,
                'shop_price'=>$value['shop_price']?$value['shop_price']:0,
                'original_price'=>$value['original_price']?$value['original_price']:0,
                'goods_brief'=>$value['goods_brief']?$value['goods_brief']:'',
                'star'=>$value['star']?$value['star']:3,
                'uid'=>$value['mid'],
                'units_id'=>$value['units_id']?$value['units_id']:0,
                'is_delete'=>$value['is_delete']?$value['is_delete']:0,
                'is_sku'=>$value['is_sku']?$value['is_sku']:0,
                'vender'=>$value['vender']?$value['vender']:'',
                'supplier'=>$value['supplier']?$value['supplier']:'',
                'trade'=>$value['trade'],
                'add_time'=>time()
                );
            if ($value['goods_img1']) {
                $goods_img = $value['goods_img1'];
                if ($value['goods_img2']) {
                    $goods_img = $value['goods_img1'].','.$value['goods_img2'];
                    if ($value['goods_img3']) {
                        $goods_img = $value['goods_img1'].','.$value['goods_img2'].','.$value['goods_img3'];
                        if ($value['goods_img4']) {
                            $goods_img = $value['goods_img1'].','.$value['goods_img2'].','.$value['goods_img3'].','.$value['goods_img4'];
                            if ($value['goods_img5']) {
                                $goods_img = $value['goods_img1'].','.$value['goods_img2'].','.$value['goods_img3'].','.$value['goods_img4'].','.$value['goods_img5'];
                            }
                        }
                    }
                }
            }
            $data['goods_img']=$goods_img;
            $agent_id = Db::name('merchants_users')->where('id',$value['mid'])->value('agent_id');
            $data['agent_id']=$agent_id?$agent_id:0;
            if (!Db::name('agent_goods')->where('bar_code',$value['bar_code'])->value('id')) {
                $goods_id = Db::name('agent_goods')->insertGetId($data);
                $desc = Db::name('goods_desc_img')->where('goods_id',$value['goods_id'])->select();
                foreach ($desc as $k => $v) {
                    $res = array(
                        'goods_id'=>$goods_id,
                        'url'=>$v['url']
                        );
                    Db::name('agent_desc')->insert($res);
                }
                if ($value['is_sku']==1) {
                    //多单位
                    $sku = Db::name('goods_sku')->where('goods_id',$value['goods_id'])->select();
                    foreach ($sku  as $ke => $vau) {
                        $result = array(
                            'goods_id'=>$goods_id,
                            'original_price'=>$vau['original_price']?$vau['original_price']:0,
                            'buy_price'=>$vau['cost']?$vau['cost']:0,
                            'shop_price'=>$vau['price']?$vau['price']:0,
                            'units_id'=>$vau['units_id']?$vau['units_id']:0,
                            'add_time'=>time()
                            );
                        $result['units_name'] = Db::name('units')->where('id',$vau['units_id'])->value('unit_name');
                        Db::name('agent_sku')->insert($result);
                    }
                }
            }
            
            
        }
        
    }

    //创建员工流水
    //
    // public function copy_order()
    // {
    //     $order = Db::name('order')->where('staff_id','neq',0)->field('order_sn,staff_id,type')->select();
    //     $count = Db::name('order')->where('staff_id','neq',0)->count();
    //     // dump($count);
    //     foreach ($order as $key => $value) {
    //         // dump($value);
    //         Db::name('pay')->where(array('remark'=>$value['order_sn']))->update(array('checker_id'=>$value['staff_id']));
    //     }
    // }

    // public function desc()
    // {
    //     $goods = GoodsModel::select();
    //     foreach ($goods as $key => $value) {
    //         for($i=1;$i<6;$i++){
    //             if ($value['goods_img'.$i]) {
    //                 if(!strpos($value['goods_img'.$i],"agent.youngport.com.cn")){
    //                     $url = $value['goods_img'.$i];
    //                     if(preg_match("/\x20*https?\:\/\/.*/i",$value['goods_img'.$i])){
    //                         $d = strpos($url,"/data/");
    //                         $u = substr($url,$d);
    //                         dump($value['goods_id']);dump($d);dump($u);dump($url);
    //                         Db::name('goods')->where('goods_id', $value['goods_id'])->update(array('goods_img'.$i=>'https://sy.youngport.com.cn'.$u));
    //                     }else{ 
    //                        $u = substr($url,1);
    //                        dump($value['goods_id']);dump($u);dump($url);
    //                        Db::name('goods')->where('goods_id', $value['goods_id'])->update(array('goods_img'.$i=>'https://sy.youngport.com.cn'.$u));
    //                     }
    //                 }
                    
    //             }
                
    //         }
    //         // die;
            
    //     }
    // }
    
}
