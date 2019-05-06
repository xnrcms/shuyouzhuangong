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
 * Author: XNRCMS<562909771@qq.com>
 * Date: 2019-04-15
 * Description:文章分类
 */

namespace app\manage\controller;

use app\manage\controller\Base;

class ArticleCategory extends Base
{
    private $apiUrl         = [];
    private $tpl            = null;

    public function __construct()
    {
        parent::__construct();
        
        $this->tpl                    = new \xnrcms\DevTpl();
        $this->apiUrl['index']        = 'api/ArticleCategory/listData';
        $this->apiUrl['edit']         = 'api/ArticleCategory/detailData';
        $this->apiUrl['add_save']     = 'api/ArticleCategory/saveData';
        $this->apiUrl['edit_save']    = 'api/ArticleCategory/saveData';
        $this->apiUrl['quickedit']    = 'api/ArticleCategory/quickEditData';
        $this->apiUrl['del']          = 'api/ArticleCategory/delData';
    }

	//列表页面
	public function index()
    {
		//参数数据接收
        $param      = request()->param();

        //初始化模板
        $listNode   = $this->tpl->showListTpl($this->getTplData('','文章分类列表','list'));
        $listId     = isset($listNode['info']['id']) ? intval($listNode['info']['id']) : 0;
        $listTag    = isset($listNode['tags']) ? $listNode['tags'] : '';

        //参数定义
        $menuid     = isset($param['menuid']) ? $param['menuid'] : 0;
        $page       = isset($param['page']) ? $param['page'] : 1;
        $search     = $this->getSearchParame($param);
        $isTree     = 1;

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

        if ($isTree === 1) {
            $Tree          = new \xnrcms\DataTree($listData);
            $listData      = $Tree->toFormatTree();
        }

        //页面头信息设置
        $pageData['isback']             = 0;
        $pageData['title1']             = '文章分类管理';
        $pageData['title2']             = '对文章分类进行添加、编辑、删除等操作';
        $pageData['notice']             = [
            '列表只是展示部分字段信息，详情请点击编辑查看.',
            '列表上可以对部分字段信息进行快速编辑'
        ];

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
        cookie('__forward__',$_SERVER['REQUEST_URI']);
        cookie('__listtag__',$listTag);

        //异步请求处理
        if(request()->isAjax())
        {
            echo json_encode(['listData'=>$this->fetch('public/list/listData'),'listPage'=>$p]);exit();
        }

        //加载视图模板
        return view();
	}

	//新增页面
	public function add()
    {
		//数据提交
        if (request()->isPost()) $this->update();

        //参数数据接收
        $param      = request()->param();

        //初始化表单模板 默认当前路由为唯一标识，自己可以自定义标识
        $formNode   = $this->tpl->showFormTpl($this->getTplData('addedit','新增/编辑文章分类表单','form'),0);
        $formId     = isset($formNode['info']['id']) ? intval($formNode['info']['id']) : 0;
        $formTag    = isset($formNode['tags']) ? $formNode['tags'] : '';
        $formList   = isset($formNode['list']) ? $formNode['list'] : [];

        //数据详情
        $info                           = $this->getDetail(0);
        $info['pid']                    = isset($param['pid']) ? (int)$param['pid'] : 0;

        //页面头信息设置
        $pageData['isback']             = 0;
        $pageData['title1']             = '';
        $pageData['title2']             = '';
        $pageData['notice']             = [];
        
        //记录当前列表页的cookie
        cookie('__forward__',$_SERVER['REQUEST_URI']);
        cookie('__formtag__',$formTag);

        //渲染数据到页面模板上
        $assignData['formId']           = $formId;
        $assignData['formTag']          = $formTag;
        $assignData['formFieldList']    = $formList;
        $assignData['info']             = $info;
        $assignData['defaultData']      = $this->getDefaultParameData();
        $assignData['pageData']         = $pageData;
        $this->assignData($assignData);

        //加载视图模板
        return view('addedit');
	}

	//编辑页面
	public function edit($id = 0)
    {
		//数据提交
        if (request()->isPost()) $this->update();

		//参数数据接收
        $param      = request()->param();

        //初始化表单模板 默认当前路由为唯一标识，自己可以自定义标识
        $formNode   = $this->tpl->showFormTpl($this->getTplData('addedit','新增/编辑文章分类表单','form'),1);
        $formId     = isset($formNode['info']['id']) ? intval($formNode['info']['id']) : 0;
        $formTag    = isset($formNode['tags']) ? $formNode['tags'] : '';
        $formList   = isset($formNode['list']) ? $formNode['list'] : [];

        //数据详情
        $info                           = $this->getDetail($id);

        //页面头信息设置
        $pageData['isback']             = 0;
        $pageData['title1']             = '';
        $pageData['title2']             = '';
        $pageData['notice']             = [];
        
        //记录当前列表页的cookie
        cookie('__forward__',$_SERVER['REQUEST_URI']);
        cookie('__formtag__',$formTag);

        //渲染数据到页面模板上
        $assignData['formId']           = $formId;
        $assignData['formTag']          = $formTag;
        $assignData['formFieldList']    = $formList;
        $assignData['info']             = $info;
        $assignData['defaultData']      = $this->getDefaultParameData();
        $assignData['pageData']         = $pageData;
        $this->assignData($assignData);

        //加载视图模板
        return view('addedit');
	}

    //数据删除
    public function del()
    {
        //参数数据接收
        $param   = request()->param();
        $ids     = (isset($param['ids']) && !empty($param['ids'])) ? $param['ids'] : $this->error('请选择要操作的数据');;
        $ids     = is_array($ids) ? implode($ids,',') : '';

        //请求参数
        $parame['uid']          = $this->uid;
        $parame['hashid']       = $this->hashid;
        $parame['id']           = $ids ;

        //请求地址
        if (!isset($this->apiUrl[request()->action()]) || empty($this->apiUrl[request()->action()]))
        $this->error('未设置接口地址');

        //接口调用
        $res       = $this->apiData($parame,$this->apiUrl[request()->action()]);
        $data      = $this->getApiData() ;

        if($res == true){

            $this->success('删除成功',Cookie('__forward__'));
        }else{
            
            $this->error($this->getApiError());
        }
    }

    //快捷编辑
	public function quickEdit()
    {
        //请求地址
        if (!isset($this->apiUrl[request()->action()]) || empty($this->apiUrl[request()->action()]))
        $this->error('未设置接口地址');
        
        //接口调用
        if ($this->questBaseEdit($this->apiUrl[request()->action()])) $this->success('更新成功');
        
        $this->error('更新失败');
    }

    //处理提交新增或编辑的数据
    private function update()
    {
        //表单数据
        $postData                = request()->param();
        if(!$this->tpl->checkFormTpl($postData)) $this->error('表单模板数据不存在');

        //定义允许提交的字段
        $allowData               = [
            'id','title','pid','sorts','seo_title','seo_keyword','seo_description','status',
        ];

        //过滤允许提交的数据
        $signData                = [];

        foreach ($postData as $key => $value) 
        {
            if (in_array($key,$allowData)) 
            {
                $signData[$key]     = $value;
            }
        }

        //用户信息
        $signData['uid']            = $this->uid;
        $signData['hashid']         = $this->hashid;

        //请求数据
        if (!isset($this->apiUrl[request()->action().'_save'])||empty($this->apiUrl[request()->action().'_save'])) 
        $this->error('未设置接口地址');

        $res       = $this->apiData($signData,$this->apiUrl[request()->action().'_save']) ;
        $data      = $this->getApiData() ;
        
        if($res){

            $this->success($signData['id']  > 0 ? '更新成功' : '新增成功',Cookie('__forward__')) ;
        }else{

            $this->error($this->getApiError()) ;
        }
    }
    
    //获取数据详情
    private function getDetail($id = 0)
    {
        $info           = [];

        if ($id > 0)
        {
            //请求参数
            $parame             = [];
            $parame['uid']      = $this->uid;
            $parame['hashid']   = $this->hashid;
            $parame['id']       = $id ;

            //请求数据
            $apiUrl     = (isset($this->apiUrl[request()->action()]) && !empty($this->apiUrl[request()->action()])) ? $this->apiUrl[request()->action()] : $this->error('未设置接口地址');
            $res        = $this->apiData($parame,$apiUrl,false);
            $info       = $res ? $this->getApiData() : $this->error($this->getApiError());
        }

        return $info;
    }

    //扩展枚举，布尔，单选，复选等数据选项
    protected function getDefaultParameData()
    {
        $defaultData['getArticleCategoryList']   = $this->getArticleCategoryList();
        return $defaultData;
    }

    private function getArticleCategoryList()
    {
        //获取列表数据
        $parame             = [];
        $parame['uid']      = $this->uid;
        $parame['hashid']   = $this->hashid;
        $parame['page']     = 1;
        $parame['search']   = '' ;

        //请求数据
        $res                = $this->apiData($parame,$this->apiUrl['index']);
        $data               = $this->getApiData();
        $listData           = [];
        $selectData         = [];

        if ($res && isset($data['lists']) && !empty($data['lists']))
        {
            $listData      = $data['lists'];
            $Tree          = new \xnrcms\DataTree($listData);
            $listData      = $Tree->toFormatTree();
        }

        if (!empty($listData))
        {
            foreach ($listData as $key => $value)
            {
                if ($value['status'] === '启用')
                {
                    $selectData[$value['id']]   = $value['title_show'];
                }
            }
        }

        return $selectData;
    }
}
?>