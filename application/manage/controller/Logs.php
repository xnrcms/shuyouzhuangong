<?php
/**
 * XNRCMS<562909771@qq.com>
 * ============================================================================
 * 版权所有 2018-2028 小能人科技有限公司，并保留所有权利。
 * ----------------------------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和使用 .
 * 不允许对程序代码以任何形式任何目的的再发布。
 * 采用TP5助手函数可实现单字母函数M D U等,也可db::name方式,可双向兼容
 * ============================================================================
 * Author: xnrcms<562909771@qq.com>
 * Date: 2018-04-11
 * Description:系统日志模块
 */

namespace app\manage\controller;

use app\manage\controller\Base;

class Logs extends Base
{
    private $apiUrl         = [];
    private $tpl            = null;

    public function __construct()
    {
        parent::__construct();

        $this->tpl                    = new \xnrcms\DevTpl();
        $this->apiUrl['done_logs']    = 'api/Logs/listData';
        $this->apiUrl['clear_logs']   = 'api/Logs/clearLogs';
    }
    public function done_logs()
    {
        $arr['search']           = ['log_type'=>1];
        //页面头信息设置
        $arr['isback']           = 0;
        $arr['title1']           = '操作日志列表';
        $arr['title2']           = '网站系统操作日志列表';
        $arr['notice']           = ['网站操作日志列表, 有平台统一管理，只有系统管理员才能清理日志.',];
        $this->extends_param     = 'log_type=1&';
        return $this->index($arr);
    }

	//列表页面
	public function index($arr = [])
    {
        //参数数据接收
        $param      = request()->param();

        //初始化模板
        $tag        = ''; //默认当前路由为唯一标识，自己可以自定义标识
        $tpl_title  = '操作日志列表'; //初始化列表模板的名称，为空时不初始化
        $tplid      = $this->tpl->initTplData(get_devtpl_tag($tag),$tpl_title,0);
        $listNode   = $this->tpl->showTpl($tplid);
        $listId     = isset($listNode['info']['id']) ? intval($listNode['info']['id']) : 0;

        //参数定义
        $menuid     = isset($param['menuid']) ? $param['menuid'] : 0;
        $page       = isset($param['page']) ? $param['page'] : 1;
        $search     = $this->getSearchParame($param);
        $isTree     = 0;

        //页面操作功能菜单
        $topMenu    = formatMenuByPidAndPos($menuid,2, $this->menu);
        $rightMenu  = formatMenuByPidAndPos($menuid,3, $this->menu);

        //获取列表数据
        $parame             = [];
        $parame['uid']      = $this->uid;
        $parame['hashid']   = $this->hashid;
        $parame['page']     = $page;
        $parame['search']   = !empty($search) ? json_encode($search) : '' ;

        //请求数据
        if (!isset($this->apiUrl[request()->action()]) || empty($this->apiUrl[request()->action()]))
        $this->error('未设置接口地址');

        $res                = $this->apiData($parame,$this->apiUrl[request()->action()]);
        $data               = $this->getApiData() ;

        $total 				= 0;
        $p 					= '';
        $listData 			= [];

        if ($res){

            //分页信息
            $page           = new \xnrcms\Page($data['total'], $data['limit']);
            if($data['total']>=1){

                $page->setConfig('theme','%FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END% %HEADER%');
                $page->setConfig('header','');
            }

            $p 				= trim($page->show());
            $total 			= $data['total'];
            $listData   	= $data['lists'];
        }

        //页面头信息设置
        $pageData['isback']             = $arr['isback'];
        $pageData['title1']             = $arr['title1'];
        $pageData['title2']             = $arr['title2'];
        $pageData['notice']             = $arr['notice'];

        //渲染数据到页面模板上
        $assignData['isTree']           = $isTree;
        $assignData['_page']            = $p;
        $assignData['_total']           = $total;
        $assignData['topMenu']          = $topMenu;
        $assignData['rightMenu']        = $rightMenu;
        $assignData['listId']           = $listId;
        $assignData['listNode']         = $listNode;
        $assignData['listData']         = $listData;
        $assignData['pageData']         = $pageData;
        $this->assignData($assignData);

        //记录当前列表页的cookie
        Cookie('__forward__',$_SERVER['REQUEST_URI']);

        //异步请求处理
        if(request()->isAjax())
        {
            echo json_encode(['listData'=>$this->fetch('public/list/listData'),'listPage'=>$p]);exit();
        }

        //加载视图模板
        return view();
	}

    //清除日志
	public function clear_logs()
    {
        //请求参数
        $parame                 = [];
        $parame['uid']          = $this->uid;
        $parame['hashid']       = $this->hashid;
        $parame['log_type']     = input('log_type',0);

        //请求地址
        if (!isset($this->apiUrl[request()->action()]) || empty($this->apiUrl[request()->action()]))
        $this->error('未设置接口地址');

        //接口调用
        $res       = $this->apiData($parame,$this->apiUrl[request()->action()]) ;
        $data      = $this->getApiData() ;

        if($res == true){

            $this->success('清理成功',Cookie('__forward__')) ;
        }else{
            
            $this->error($this->getApiError()) ;
        }
    }
}
?>