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
 * Date: 2018-02-08
 * Description:系统功能表单模板管理
 */

namespace app\manage\controller;

use app\manage\controller\Base;

/**
 * 后台表单模板控制器
 */
class Devform extends Base
{
	private $apiUrl         = [];

    public function __construct()
    {
        parent::__construct();

        $this->apiUrl['index']        = 'admin/Devform/listData';
        $this->apiUrl['edit']         = 'admin/Devform/detailData';
        $this->apiUrl['save_data']    = 'admin/Devform/saveData';
        $this->apiUrl['quickedit']    = 'admin/Devform/quickEditData';
        $this->apiUrl['del']          = 'admin/Devform/delData';
        $this->apiUrl['release']      = 'admin/Devform/releaseData';
    }

	/**
	 * 表单列表
	 * @author xxx
	 */
	public function index()
	{
		//获取列表数据
		$search 			= [];
		$parame 			= [];
		$parame['uid']		= $this->uid;
        $parame['hashid']	= $this->hashid;
		$parame['pid']		= 0;
        $parame['page']     = input('page',1);
        $parame['search']   = !empty($search) ? json_encode($search) : '' ;

        //请求数据
        if (!isset($this->apiUrl[request()->action()]) || empty($this->apiUrl[request()->action()])) 
        $this->error('未设置接口地址');

    	$res                = $this->apiData($parame,$this->apiUrl[request()->action()]);
        $allDevform         = $this->getApiData() ;
        $list 				= (!empty($allDevform) && isset($allDevform['lists'])) ? $allDevform['lists'] : [];
		$fieldList			= [];
		$fieldInfo 			= ['id'=>0,'pid'=>$parame['pid'],'require'=>0];

		if (!empty($list)){
			//获取表单模板字段数据
			$search 			= [];
			$parame 			= [];
			$parame['uid']		= $this->uid;
	        $parame['hashid']	= $this->hashid;
			$parame['pid']		= $list[0]['id'];
	        $parame['page']     = input('page',1);
	        $parame['search']   = !empty($search) ? json_encode($search) : '' ;

			$res                = $this->apiData($parame,$this->apiUrl[request()->action()]);
        	$allDevform         = $this->getApiData() ;
	        $fieldList 			= (!empty($allDevform) && isset($allDevform['lists'])) ? $allDevform['lists'] : [];
			
			if (!empty($fieldList)) {
				$firstid	= $fieldList[0]['id'];
				$firstpid	= $fieldList[0]['pid'];

				$parame 			= [];
				$parame['uid']		= $this->uid;
		        $parame['hashid']	= $this->hashid;
				$parame['id']		= $firstid;

		        $res                = $this->apiData($parame,$this->apiUrl['edit']);
				$fieldInfo			= $res  ? $this->getApiData() : $fieldInfo;
			}
		}

		//页面数据
		$pageData						= [];
		$pageData['isback']     		= 0;
        $pageData['title1']     		= '开发 - 系统表单模板管理 ';
        $pageData['title2']     		= '系统表单模板添加/删除/编辑操作';
        $pageData['notice']     		= ['温馨提示：新增表单模板请点击第一栏加号','新增表单字段请先选择第一栏表单，再点击第二栏的加号'];

        //记录当前列表页的cookie
		cookie('__forward__',$_SERVER['REQUEST_URI']);
		
        //渲染数据到页面模板上
		$assignData['_list'] 			= $list;
		$assignData['_fieldList'] 		= $fieldList;
		$assignData['_fieldInfo'] 		= $fieldInfo;
		$assignData['pageData'] 		= $pageData;
		$this->assignData($assignData);

		//加载视图模板
		return view();
	}

	/**
	 * 新增数据
	 */
	public function add()
	{
		//数据提交
		if (request()->isPost()) $this->update();

		//数据详情
        $info                           = $this->getDetail(0);
		$info['status']					= 1;

		//渲染数据到页面模板上
		$assignData['info'] 			= $info;
		$this->assignData($assignData);

		//加载视图模板
		return view('addedit');
	}

	/**
	 * 编辑数据
	 */
	public function edit($id = 0)
	{
		//数据提交
		if (request()->isPost()) $this->update();

		//数据详情
        $info                           = $this->getDetail($id);
		if(empty($info)) $this->error('数据获取失败',Cookie('__forward__'));

		//渲染数据到页面模板上
		$assignData['info'] 			= $info;
		$this->assignData($assignData);

		//加载视图模板
		return view('addedit');
	}

	/**
	 * 删除数据
	 */
	public function del()
	{
		$ids			= request()->param();
		$ids 			= (isset($ids['ids']) && !empty($ids['ids'])) ? $ids['ids'] : [];

		//请求地址
        if (!isset($this->apiUrl[request()->action()])||empty($this->apiUrl[request()->action()])) 
        $this->error('未设置接口地址');

		if ( empty($ids) ) $this->error('请选择要操作的数据!');

		$ids 				= is_array($ids) ? implode(',',$ids) : intval($ids);

		$parame 			= [];
		$parame['uid']		= $this->uid;
        $parame['hashid']	= $this->hashid;
		$parame['id']		= $ids;

       	//接口调用
        $res       = $this->apiData($parame,$this->apiUrl[request()->action()]) ;
        $data      = $this->getApiData() ;

		if($res){

			//数据返回
			$this->success('删除成功',Cookie('__forward__'));
		} else {

			$this->error($this->getApiError()) ;
		}
	}

	//提交表单
	protected function update()
	{
		//提交安全过滤
		if (!request()->isPost()) $this->error('非法提交！');

        //表单数据
        $postData                = request()->param();

        //接口数据
        $signData                   = [];
        $signData['uid']            = $this->uid;
        $signData['hashid']         = $this->hashid;
        $signData['title']			= isset($postData['title']) ? trim($postData['title']) : '';
        $signData['status'] 		= isset($postData['status']) ? (int)$postData['status'] : 2;
        $signData['sort'] 			= isset($postData['sort']) ? (int)$postData['sort'] : 1;
        $signData['tag']			= isset($postData['tag']) ? trim($postData['tag']) : '';
        $signData['cname']			= isset($postData['cname']) ? trim($postData['cname']) : '';
        $signData['id'] 			= isset($postData['id']) ? (int)$postData['id'] : 0;
        $signData['pid'] 			= isset($postData['pid']) ? (int)$postData['pid'] : 0;

        $config 				 	= [];

        if($signData['pid'] > 0)
        {    	
            $config['title']        = $signData['title'];
            $config['tag']          = $signData['tag'];
            $config['type']         = isset($postData['type']) ? trim($postData['type']) : '';
            $config['group']        = isset($postData['group']) ? trim($postData['group']) : '';
            $config['require']      = isset($postData['require']) ? (int)$postData['require'] : 0;
            $config['add']          = isset($postData['add']) ? (int)$postData['add'] : 0;
            $config['edit']         = isset($postData['edit']) ? (int)$postData['edit'] : 0;
            $config['notice']       = isset($postData['notice']) ? (int)$postData['notice'] : 0;
            $config['default']      = isset($postData['default']) ? trim($postData['default']) : '';
        	$config['field_value']	= isset($postData['field_value']) ? trim($postData['field_value']) : '';
        	$config['attr']	  		= isset($postData['attr']) ? trim(str_replace(["\r\n","\r","\n"], " ",$postData['attr'])) : '';
        }

		$signData['config']			= !empty($config) ? json_encode($config) : '';

		if (!isset($this->apiUrl['save_data']) || empty($this->apiUrl['save_data']))
		$this->error('未设置接口地址');

		//请求数据
        $res       = $this->apiData($signData,$this->apiUrl['save_data']) ;
        $devform   = $this->getApiData() ;

		if($res && !empty($devform))
		{
			$devform['ac']  	= $signData['id'] > 0 ? 1 : 0;
			$devform['title'] 	= $signData['title'];
			$devform['pid'] 	= $signData['pid'];
			$devform['status'] 	= $signData['status'];

			//数据返回
			$html 				= $this->getHtmls($devform);

			$this->success($signData['id'] >0 ? '更新成功' : '新增成功','', array_merge($devform,['htmls'=>$html]));
		}
		else
		{
			$error = $this->getApiError();
			$this->error(empty($error) ? '未知错误！' : $error);
		}
	}

	public function release()
	{
		$ids			= request()->param();
		$ids 			= (isset($ids['ids']) && !empty($ids['ids'])) ? $ids['ids'] : [];

		//请求地址
        if (!isset($this->apiUrl[request()->action()])||empty($this->apiUrl[request()->action()])) 
        $this->error('未设置接口地址');

		if ( empty($ids) ) $this->error('请选择要操作的数据!');

		$ids 				= is_array($ids) ? implode(',',$ids) : intval($ids);

		$parame 			= [];
		$parame['uid']		= $this->uid;
        $parame['hashid']	= $this->hashid;
		$parame['id']		= $ids;

       	//接口调用
        $res       = $this->apiData($parame,$this->apiUrl[request()->action()]) ;
        $data      = $this->getApiData() ;

		if($res)
		{
			//数据返回
			$this->success('发布成功',Cookie('__forward__'));
		} else {

			$this->error($this->getApiError()) ;
		}
	}

	public function changeFieldList(){

		$id 		= intval(input('post.id'));

		$fieldList	= $this->getFormField($id);

		$this->assign('_fieldList', $fieldList);

		$content 	= $this->fetch('filed_list');

		$firstid 	= 0;
		$firstpid 	= $id;
		if (!empty($fieldList))
		{
			$firstid	= $fieldList[0]['id'];
			$firstpid	= $fieldList[0]['pid'];
		}

		return json(['content'=>$content,'id'=>$firstid,'pid'=>$firstpid]);
	}
	
	public function changeFieldInfo()
	{	

		$param 		= request()->param();
		$pid 		= intval($param['pid']);
		$id 		= intval($param['id']);

		if ($pid <=0 && $id <= 0) $fieldInfo 	= [];

		$fieldInfo 	= ['id'=>$id,'pid'=>$pid,'status'=>1,'require'=>0,'type'=>'string','edit'=>0,'search'=>0];
		if ($id >0)
		{
			$tpl 			= new \xnrcms\DevTpl(1);
    		$fieldInfo 		= $tpl->getTplById($id);

			//数据格式化
            if($fieldInfo['pid'] > 0)
            {
                $field 			= json_decode($fieldInfo['config'] , true);
                $field['attr'] 	= !empty($field['attr']) ? str_replace(' ',"\r", $field['attr']): '' ;
                $fieldInfo 		= array_merge($fieldInfo,$field) ;
            }
		}

		//渲染数据到页面模板上
		$assignData['_fieldInfo'] 		= $fieldInfo;
		$this->assignData($assignData);

		//加载视图模板
		return view('filed_info');
	}

	protected function getHtmls($data)
	{	
		if ($data['ac'] == 1) return '';

		$editUrl 		= url('Devform/edit',['id'=>$data['id']]);
		$delUrl 		= url('Devform/edit',['id'=>$data['id']]);
		$quickEditUrl 	= url('Devform/quickEdit',['id'=>$data['id']]);
		$cloneFormUrl 	= url('Devform/cloneForm',['ids'=>$data['id']]);
		$releaseUrl 	= url('Devform/release',['ids'=>$data['id']]);

		$htmls = '<tr id="devform_id_'.$data['id'].'" data-id ="'.$data['id'].'" data-pid ="'.$data['pid'].'" >
                <td align="left" class="handle" width="78%">
                  <div>
                    <span class="btn"><em><i class="fa fa-cog"></i>'.$data['title'].'<i class="arrow"></i></em>
                    <ul>
                      <li><a onClick="return layer_show(\'表单模板编辑\',\''.$editUrl.'\',500,350);" href="javascript:;">编辑</a></li>
                      <li><a onClick="layer_show(\'克隆表单\',\''.$cloneFormUrl.'\',1100,550);" href="javascript:;">克隆表单</a></li>
                      <li><a onClick="delfun(this,\'确认发布表单模板吗？\',1)" href="javascript:;" data-url="'.$releaseUrl.'">发布表单</a></li>
                      <li><a onClick="delfun(this,\'确认删除表单模板吗？\')" href="javascript:;" data-url="'.$delUrl.'">删除</a></li>
                    </ul>
                    </span>
                  </div>
                </td>

                <td align="center" class="" width="22%">
                  <div data-yes="启用" data-no="禁用">';
        if ($data['status'] == 1) {

        	$htmls .= '<span class="yes" onClick="CommonJs.quickEdit(this,\''.$quickEditUrl.'\',"status",\''.$data['id'].'\');" ><i class="fa fa-check-circle"></i>启用</span>';
        }else{

        	$htmls .= ' <span class="no" onClick="CommonJs.quickEdit(this,\''.$quickEditUrl.'\',"status",\''.$data['id'].'\');" ><i class="fa fa-ban"></i>禁用</span>';
        }
                    
        $htmls .= '</div></td></tr>';

        return $htmls;
	}

	public function quickEdit()
	{
		//请求地址
        if (!isset($this->apiUrl[request()->action()]) || empty($this->apiUrl[request()->action()]))
        $this->error('未设置接口地址');
        
        //接口调用
        if ($this->questBaseEdit($this->apiUrl[request()->action()])) $this->success('更新成功');
        
        $this->error('更新失败');
	}

	//表单字段列表
	private function getFormField($pid = 0)
	{
		$search 			= [];
		$parame 			= [];
		$parame['uid']		= $this->uid;
        $parame['hashid']	= $this->hashid;
		$parame['pid']		= $pid;
        $parame['page']     = input('page',1);
        $parame['search']   = !empty($search) ? json_encode($search) : '' ;

        $res                = $this->apiData($parame,$this->apiUrl['index']);
        $allDevform         = $this->getApiData() ;

        $devform 			= (!empty($allDevform) && isset($allDevform['lists'])) ? $allDevform['lists'] : [];

		return $res ? $devform : [];
	}

	//表单模板快速设置
	public function set_form($id = 0)
	{
		//数据提交
		if (request()->isPost()) $this->set_form_update();

		$parame 			= [];
		$parame['uid']		= $this->uid;
        $parame['hashid']	= $this->hashid;
		$parame['pid']		= $id;
		$parame['page']		= 1;
		$parame['search']	= '';

        $res                = $this->apiData($parame,$this->apiUrl['index']);
        $allDevform         = $this->getApiData() ;

        $devfrom 			= (!empty($allDevform) && isset($allDevform['lists'])) ? $allDevform['lists'] : [];
		$fieldList 			= $res ? $devfrom : [];

		if(!empty($fieldList))
		{
			foreach ($fieldList as $key => $value)
			{
				foreach ($value as $kk => $vv)
				{
					if ($kk == 'config')
					{
						$fieldList[$key][$kk] 	= json_decode($vv,true);
					}
				}

				cache(md5("admin/Devform/detailData".$value['id']),$value);
			}
		}

		//记录当前列表页的cookie
        Cookie('__forward__',$_SERVER['REQUEST_URI']);

		//渲染数据到页面模板上
		$assignData['formPid'] 			= $id;
		$assignData['fieldList'] 		= $fieldList;
		$this->assignData($assignData);

		//加载视图模板
		return view();
	}

	//提交表单
	protected function set_form_update()
	{
		//提交安全过滤
		if (!request()->isPost()) $this->error('非法提交！');

        //表单数据
        $postData                = request()->param();

        //接口数据
        $signData                   = [];
        $signData['uid']            = $this->uid;
        $signData['hashid']         = $this->hashid;
        $signData['title']			= isset($postData['title']) ? trim($postData['title']) : '';
        $signData['status'] 		= isset($postData['status']) ? (int)$postData['status'] : 2;
        $signData['sort'] 			= isset($postData['sort']) ? (int)$postData['sort'] : 1;
        $signData['tag']			= isset($postData['tag']) ? trim($postData['tag']) : '';
        $signData['cname']			= isset($postData['cname']) ? trim($postData['cname']) : '';
        $signData['id'] 			= isset($postData['id']) ? (int)$postData['id'] : 0;
        $signData['pid'] 			= isset($postData['pid']) ? (int)$postData['pid'] : 0;

        if ($signData['pid'] <= 0) $this->error('表单模板数据不存在！');

        if ($signData['id'] > 0)
        {
        	$info 				= cache(md5("admin/Devform/detailData".$signData['id']));
        	if (empty($info)) $this->error('表单模板数据不存在');

        	$config 				= (isset($info['config']) && !empty($info['config'])) ? json_decode($info['config'],true) : [];
        	$postData['default'] 	= isset($config['default']) ? $config['default'] : '';
        	$postData['notice'] 	= isset($config['notice']) ? $config['notice'] : '';
        	$postData['attr'] 		= isset($config['attr']) ? $config['attr'] : '';
        }

        $config 				= [];
        $config['title']        = $signData['title'];
        $config['tag']          = $signData['tag'];
        $config['type']         = isset($postData['type']) ? trim($postData['type']) : '';
        $config['group']        = isset($postData['group']) ? trim($postData['group']) : '';
        $config['require']      = isset($postData['require']) ? (int)$postData['require'] : 0;
        $config['add']          = isset($postData['add']) ? (int)$postData['add'] : 0;
        $config['edit']         = isset($postData['edit']) ? (int)$postData['edit'] : 0;
        $config['notice']       = isset($postData['notice']) ? (int)$postData['notice'] : 0;
        $config['default']      = isset($postData['default']) ? trim($postData['default']) : '';
    	$config['field_value']	= isset($postData['field_value']) ? trim($postData['field_value']) : '';
    	$config['attr']	  		= isset($postData['attr']) ? trim(str_replace(["\r\n","\r","\n"], " ",$postData['attr'])) : '';
        	
		$signData['config']		= json_encode($config);

		//请求数据
        $res       			= $this->apiData($signData,$this->apiUrl['save_data']) ;
        $devform   			= $this->getApiData() ;

		if($res && !empty($devform))
		{
			$this->success($signData['id'] >0 ? '更新成功' : '新增成功', Cookie('__forward__'));
		}
		else
		{
			$error = $this->getApiError();
			$this->error(empty($error) ? '未知错误！' : $error);
		}
	}

	public function cloneForm($id =0)
	{
        //数据提交
		if (request()->isPost()) $this->clone_update();

		$parame 			= [];
		$parame['uid']		= $this->uid;
        $parame['hashid']	= $this->hashid;
		$parame['pid']		= $id;
		$parame['page']		= 1;
		$parame['search']	= '';

        $res                = $this->apiData($parame,$this->apiUrl['index']);
        $allDevform         = $this->getApiData() ;

        $devfrom 			= (!empty($allDevform) && isset($allDevform['lists'])) ? $allDevform['lists'] : [];

		$fieldList 			= $res ? $devfrom : [];

		if(!empty($fieldList))
		{
			foreach ($fieldList as $key => $value)
			{
				foreach ($value as $kk => $vv)
				{
					if ($kk == 'config')
					{
						$fieldList[$key][$kk] 	= json_decode($vv,true);
					}
				}

				cache(md5("admin/Devform/detailData".$value['id']),$value);
			}
		}

		//记录当前列表页的cookie
        Cookie('__forward__',$_SERVER['REQUEST_URI']);

        //渲染数据到页面模板上
		$assignData['lid'] 				= $id;
		$assignData['fieldList'] 		= $fieldList;
		$this->assignData($assignData);

		//加载视图模板
		return view();
	}

	protected function clone_update()
	{
		if(request()->isPost())
		{
			$param 					= request()->param();
			$form_title 			= input('form_title');
			$clone 					= (isset($param['clone']) && !empty($param['clone'])) ? $param['clone'] : [];

			if (empty($form_title)) $this->error('表单名称不能为空');
			if (empty($clone)) $this->error('克隆数据不能为空');

			$parame 				= [];
			$parame['uid']			= $this->uid;
	        $parame['hashid']		= $this->hashid;
	        $parame['formname']		= $form_title;
	        $parame['formid']		= intval(input('formId'));
	        $parame['cloneData']	= json_encode($clone);

	        $res 					= $this->apiData($parame,'admin/Devform/saveClone');
	        if($res)
	        {
				$this->success( '克隆成功', Cookie('__forward__'));
			}
			else
			{
				$error = $this->getApiError();
				$this->error(empty($error) ? '未知错误！' : $error);
			}
		}

		$this->error('非法提交！');
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
            $res        = $this->apiData($parame,$apiUrl);
            $info       = $res ? $this->getApiData() : $this->error($this->getApiError());
        }

        return $info;
    }

    public function setFormField($id=0)
    {
    	$param 					= request()->param();
    	if(request()->isPost())
    	{
    		$tpl 				= new \xnrcms\DevTpl(1);
    		$res 				= $tpl->saveTplData($param);
    		$msg 				= $res == 1 ? '设置成功' : '新增成功';
	        $this->success( $msg, Cookie('__forward__'));
    	}

    	if ($id<= 0)
    	{
    		$info['id'] 	= 0;
    		$info['pid'] 	= $param['pid'];
    		$info['edit'] 	= 0;
    		$info['search'] = 0;
    	}else{

    		$tpl 			= new \xnrcms\DevTpl(1);
    		$info 			= $tpl->getTplById($id);
    	}

    	$config 	= (isset($info['config']) && !empty($info['config'])) ? $info['config'] : [];
    	$config 	= !empty($config) ? json_decode($config,true) : [];
    	$info 		= !empty($info) ? array_merge($info,$config) : [];

    	if (!empty($info))
    	{
    		$info['fdone'] 	= [$info['add'] == 1 ? 1 : 0,$info['edit'] == 1 ? 2 : 0];
    	}

    	//表单模板
        $formData                       = $this->formNote(1);

    	//页面头信息设置
        $pageData['isback']             = 0;
        $pageData['title1']             = '';
        $pageData['title2']             = '';
        $pageData['notice']             = [];

        //渲染数据到页面模板上
        $assignData['formId']           = 1;
        $assignData['info']             = $info;
        $assignData['formFieldList']    = $formData['list'];
        $assignData['_fieldInfo']       = $info;
        $assignData['defaultData']      = $this->getDefaultParameData();
        $assignData['pageData']         = $pageData;
        $this->assignData($assignData);

        //加载视图模板
        return view();
    }

    public function delFormField()
    {
    	$param 		= request()->param();
    	$ids     	= (isset($param['ids']) && !empty($param['ids'])) ? $param['ids'] : $this->error('请选择要操作的数据');
    	$tpl 				= new \xnrcms\DevTpl(1);
    	$res 				= $tpl->delTplData($ids[0]);

    	$res == 1 ? $this->success( '删除成功', Cookie('__forward__')) : $this->error('删除失败');
    }

    private function formNote($isEdit)
    {
        $formNote   = [] ;
        $formNote[] = ['id'=>0,'title'=>'ID','tag'=>'pid','type'=>'hidden','require'=>'0',"add"=>"1","edit"=>"1","notice"=>"","group"=>"","default"=>"0","attr"=>""] ;
        $formNote[] = ['id'=>0,'title'=>'ID','tag'=>'id','type'=>'hidden','require'=>'1',"add"=>"1","edit"=>"1","notice"=>"","group"=>"","default"=>"","attr"=>""] ;
        //新订单处理处理
        $formNote[] = ['id'=>0,'title'=>'字段名称','tag'=>'title','type'=>'string','require'=>'1',"add"=>"1","edit"=>"1","notice"=>"字段名称","group"=>"","default"=>"","attr"=>""];
        $formNote[] = ['id'=>0,'title'=>'字段标识','tag'=>'tag','type'=>'string','require'=>'1',"add"=>"1","edit"=>"1","notice"=>"获取数据的数据下标","group"=>"","default"=>"","attr"=>"cols='50'"];
        $formNote[] = ['id'=>0,'title'=>'字段类型','tag'=>'type','type'=>'select','require'=>'1',"add"=>"1","edit"=>"1","notice"=>"字段类型默认是字符串","group"=>"","default"=>"parame:type","attr"=>""] ;
        $formNote[] = ['id'=>0,'title'=>'分组标识','tag'=>'group','type'=>'string','require'=>'0',"add"=>"1","edit"=>"1","notice"=>"分组标识，默认可不填写","group"=>"","default"=>"","attr"=>""] ;
        $formNote[] = ['id'=>0,'title'=>'是否必填','tag'=>'require','type'=>'bool','require'=>'0',"add"=>"1","edit"=>"1","notice"=>"","group"=>"","default"=>"string:1=是,0=否","attr"=>"cols='50'"];
        $formNote[] = ['id'=>0,'title'=>'字段默认值','tag'=>'field_value','type'=>'string','require'=>'0',"add"=>"1","edit"=>"1","notice"=>"字段默认值","group"=>"","default"=>"string:1=可编辑,2=可搜索","attr"=>""] ;
        $formNote[] = ['id'=>0,'title'=>'使用场景','tag'=>'fdone','type'=>'checkbox','require'=>'0',"add"=>"1","edit"=>"1","notice"=>"该字段字段功能","group"=>"","default"=>"string:1=新增,2=编辑","attr"=>""] ;
        $formNote[] = ['id'=>0,'title'=>'字段提示','tag'=>'notice','type'=>'string','require'=>'0',"add"=>"1","edit"=>"1","notice"=>"字段下方小提示","group"=>"","default"=>"parame:parameName","attr"=>"cols='50'"];
        $formNote[] = ['id'=>0,'title'=>'字段属性','tag'=>'attr','type'=>'textarea','require'=>'0',"add"=>"1","edit"=>"1","notice"=>"字段属性设置每行一个","group"=>"","default"=>"parame:parameName","attr"=>"cols='50'"];
        $formNote[] = ['id'=>0,'title'=>'字段附加数据','tag'=>'default','type'=>'textarea','require'=>'0',"add"=>"1","edit"=>"1","notice"=>"字段附加数据是单选、多选、枚举、布尔的选项数据，其他类型此处填写无效填写示例:<br>字符串：string:1=张三,2=李四,....<br>成员变量：parame:parameName<br>","group"=>"","default"=>"","attr"=>"cols='50'"];
        $formNote[] = ['id'=>0,'title'=>'字段排序','tag'=>'sort','type'=>'string','require'=>'0',"add"=>"1","edit"=>"1","notice"=>"","group"=>"","default"=>"","attr"=>""];
        $formNote[] = ['id'=>0,'title'=>'状态','tag'=>'status','type'=>'bool','require'=>'0',"add"=>"1","edit"=>"1","notice"=>"","group"=>"","default"=>"string:1=启用,2=禁用","attr"=>"cols='50'"];

        $info['id']			= '1';
        $tpl 				= new \xnrcms\DevTpl();
        $list       		= $tpl->formatFormTplData($formNote,$info,$isEdit) ;
        return $list ;
    }

    protected function getDefaultParameData()
    {
    	$defaultData['type']   = config('extend.form_type_list');
        return $defaultData;
    }
}
?>