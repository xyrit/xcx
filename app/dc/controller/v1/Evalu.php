<?php
namespace app\dc\controller\v1;
use app\dc\model\v1\Evalu as EvaluModel;
use app\dc\model\v1\Picture;
use app\dc\model\v1\User;
use app\dc\model\v1\Store;
use app\dc\model\v1\Order;
use think\Controller;
use think\Db;
use think\Validate;
class Evalu extends Home
{

    /**
     * 获取用户评价
     * @Param 
     */
    public function get_xcx_eval()
    {
        $mid = input('mid');
        if (!$mid) {
            err('mid is empty');
        }
        $store = new Store;
        $id =  $store->_get_mch_id($mid); //获取商户id
        $eval = new EvaluModel;
        $res =  $eval->get_eval($id);
        succ($res);
    }

    /**
     * 用户评价
     * @Param 
     */
    public function set_xcx_eval()
    {
        $data = array();
        ($data['star'] = input('star')) || err('star is empty');  //评价星级
        ($data['eval'] = input('content')) || err('content is empty'); //内容
        ($data['order_id'] = input('order_id')) || err('order_id is empty');  //订单Id
        // $data['mid'] = input('mid');    //商户id
        $order_id = input('order_id');
        $user_id = Order::where(array('order_id'=>$order_id))->field('user_id,mid,order_status,is_eval')->find();
        if ($user_id['order_status'] == 5 && $user_id['is_eval'] == 0) {
            $store = new Store;
            $data['mid'] =  $store->_get_mch_id($user_id['user_id']);
            // dump($data);die;
            
            $memid = $user_id['mid'];
            if ($memid != UID) {
                err('您没有评价权限');
            }
            $data['memid'] = UID;
            $data['add_time'] = time();
            // $data['order_id'] = input('order_id');  //订单Id
            $data['img'] = input('img');    //图片id
            $rules = [
                ['content','require|max:100','内容为空|内容在100字以内'],
                ['star','require','星级为空']
            ];
            if(EvaluModel::where('order_id',$order_id)->find()){
                Order::where(array('order_id'=>$order_id))->update(['is_eval' => 1,'update_time'=>time()]);
                err('该订单已评价');
            }
            $validate = new Validate($rules);
            $res = EvaluModel::create($data);
            if($res){

                Order::where(array('order_id'=>$order_id))->update(['is_eval' => 1,'update_time'=>time()]);
                succ('提交成功');
            }else{
                err('提交失败');
            }
        } else if($user_id['order_status'] == 5 && $user_id['is_eval'] == 1){
            err('该订单已评价');
        }else{
            err('没有评价权限！');
        }
        
    }
    
}