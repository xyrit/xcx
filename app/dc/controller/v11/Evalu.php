<?php
namespace app\dc\controller\v1;
use app\dc\model\v1\Evalu as EvaluModel;
use app\dc\model\v1\Picture;
use app\dc\model\v1\User;
use think\Controller;
use think\Db;
use think\Validate;
class Evalu extends Home
{
    
    /**
     * 获取用户评价
     * @Param uid 商家uid
     */
    public function get_xcx_eval(EvaluModel $Evalu)
    {
        $mid = input('mid');
        // $res = $Evalu->where('mid',$mid)->find();
        // dump($res);die;
        $res = $Evalu->get_eval($mid);
        // dump($res);die;
        if($res){
            succ($res);
        }else{
            err('获取评价失败');
        }
    }

    /**
     * 用户评价
     * @Param uid 商家uid
     */
    public function set_xcx_eval()
    {
        $data = array();
        ($data['star'] = input('star')) || err('star is empty');  //评价星级
        ($data['eval'] = input('eval')) || err('eval is empty'); //内容
        $data['mid'] = input('mid');    //商户id
        $data['memid'] = UID;
        $data['order_id'] = input('order_id');  //订单Id
        $data['img'] = input('img');    //图片id
        $rules = [
            ['eval','require|max:100','内容为空|内容在100字以内'],
            ['star','require','星级为空']
        ];
        $validate = new Validate($rules);
        $res = EvaluModel::create($data);
        if($res){succ('提交成功');}else{err('提交失败');}
    }
    
}
