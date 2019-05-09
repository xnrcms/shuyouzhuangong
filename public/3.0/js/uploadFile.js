function GetUploadify(uploadUrl,title)
{
    layer.open({
        type: 2,
        title: !title ? '上传图片' : title,
        shadeClose: true,
        shade: false,
        maxmin: true, //开启最大化最小化按钮
        area: ['50%', '60%'],
        content: uploadUrl
    });
}

function call_img_back(input,fileurl_tmp,fileurl_ids)
{
	if(typeof fileurl_ids =='object' && typeof fileurl_tmp == 'object')
    {
	    //循环显示图片
		var imgs = '';
		var imgids = '' ;

		for (var i=0 ; i< fileurl_tmp.length ; i++)
        {
            imgs += "<div class='c-img-box'>"
            imgs += "<span class='close' data-id="+fileurl_ids[i]+">X</span>"
			imgs += "<img height='150px' src="+fileurl_tmp[i]+">" ;
            imgs += "</div>"
		}

        for (var i=0 ; i< fileurl_ids.length ; i++)
        {
             imgids += fileurl_ids[i]+',';
        }

        imgids = imgids.substr(0,imgids.length-1) ;

        $("#image_"+input).parent().append(imgs) ;

        $(".close").click(function () {
            var imgid = $(this).attr('data-id') ;
			$(this).parent().remove() ;


			var id = '' ;
			var imgbox = $(".c-img-box") ;

            for (var i=0 ; i<imgbox.length ; i++ ){
                id += imgbox.eq(i).find('span').attr('data-id')+',' ;

			}

			id = id.substr(0,id.length-1) ;

            $("input[name='"+input+"']").val(id) ;

        });

        var oldimgids = $("input[name='"+input+"']").val() ;
        if(oldimgids != '')
        {
            imgids=oldimgids+','+imgids ;
		}

        $("input[name='"+input+"']").val(imgids) ;
	}else{
        $("#image_"+input).attr('src',fileurl_tmp) ;
        $("input[name='"+input+"']").val(fileurl_ids) ;
	}
}

function call_file_back(input,fileurl_tmp,fileurl_ids)
{
    if(typeof fileurl_ids =='object' && typeof fileurl_tmp == 'object')
    {
        //后期扩展多个文件显示
        /*//循环显示图片
        var imgs = '';
        var imgids = '' ;
        for (var i=0 ; i< fileurl_tmp.length ; i++){
            imgs += "<div class='c-img-box'>"
            imgs += "<span class='close' data-id="+fileurl_ids[i]+">X</span>"
            imgs += "<img height='150px' src="+fileurl_tmp[i]+">" ;
            imgs += "</div>"
        }
        for (var i=0 ; i< fileurl_ids.length ; i++){
            imgids += fileurl_ids[i]+',';
        }
        imgids = imgids.substr(0,imgids.length-1) ;

        $("#image_"+input).parent().append(imgs) ;

        $(".close").click(function () {
            var imgid = $(this).attr('data-id') ;
            $(this).parent().remove() ;


            var id = '' ;
            var imgbox = $(".c-img-box") ;

            for (var i=0 ; i<imgbox.length ; i++ ){
                id += imgbox.eq(i).find('span').attr('data-id')+',' ;
            }

            id = id.substr(0,id.length-1) ;

            $("input[name='"+input+"']").val(id) ;

        });

        var oldimgids = $("input[name='"+input+"']").val() ;
        if(oldimgids != ''){
            imgids=oldimgids+','+imgids ;
        }

        $("input[name='"+input+"']").val(imgids) ;*/
    }else{
        var ext             = fileurl_tmp.split('.');
            ext             = ext[ext.length - 1];
        var src_path        = '/3.0/package/webuploader/images/'+ext+'.png';
        if (!CheckImgExists(src_path))
        {
            src_path        = '/3.0/package/webuploader/images/def.png';
        }

        $("#image_"+input).attr("width","150").attr('src',src_path) ;
        $("input[name='"+input+"']").val(fileurl_ids) ;
    }
}

function CheckImgExists(imgurl) 
{  
    var ImgObj = new Image(); //判断图片是否存在  
    ImgObj.src = imgurl;  
    //没有图片，则返回-1  
    if (ImgObj.fileSize > 0 || (ImgObj.width > 0 && ImgObj.height > 0)) {  
        return true;  
    } else {  
        return false;
    }  
} 