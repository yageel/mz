<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>公众号管理-美客系统管理</title>
    <meta name="keywords" content="美客系统管理">
    <meta name="description" content="美客系统管理">
    <link rel="shortcut icon" href="favicon.ico">
    <link href="/Public/css/bootstrap.min.css?v=3.3.6" rel="stylesheet">
    <link href="/Public/css/font-awesome.css?v=4.4.0" rel="stylesheet">
    <link href="/Public/css/plugins/iCheck/custom.css" rel="stylesheet">
    <link href="/Public/css/animate.css" rel="stylesheet">
    <link href="/Public/css/style.css?v=4.0.0" rel="stylesheet">
    <script type="text/javascript" charset="utf-8" src="/Public/js/plugins/ueditor/ueditor.config.js"></script>
    <script type="text/javascript" charset="utf-8" src="/Public/js/plugins/ueditor/ueditor.all.js"></script>
    
    <base target="_self">
    <style type="text/css">
        .view_image_block{ position: absolute; width: 200px; height: 200px; display: table-cell; overflow: hidden; background: #fff; border: 1px #ddd solid; z-index: 1000;}
        .view_image_block img{ max-width: 200px; text-align: center; margin: 0 auto;}
        .ticketinfo{display:none;}
    </style>
</head>
<body class="gray-bg">
<div class="wrapper wrapper-content animated fadeInRight">
    
  <div class="row">
    <div class="col-sm-12">
      <div class="ibox float-e-margins">
        <div class="ibox-title">
          <h5>公众号列表</h5>
          <!--
          <div class="ibox-tools"> <a class="collapse-link"> <i class="fa fa-chevron-up"></i> </a> <a class="dropdown-toggle" data-toggle="dropdown" href="table_data_tables.html#"> <i class="fa fa-wrench"></i> </a>
            <ul class="dropdown-menu dropdown-user">
              <li><a href="table_data_tables.html#">选项1</a> </li>
              <li><a href="table_data_tables.html#">选项2</a> </li>
            </ul>
            <a class="close-link"> <i class="fa fa-times"></i> </a> </div>
        </div>-->
          <div class="ibox-content">
            <div class=""> <a href="<?php echo U('/city/edit');?>" target="_self" class="btn btn-primary ">添加公众号</a> </div>
            <table class="table table-striped table-bordered table-hover " id="editable">
              <thead>
                <tr>
                  <th>编号</th>
                  <th>名称</th>
                  <th>AppId</th>
                  <th>商户号</th>
                  <th>开放平台</th>
                  <th>添加时间</th>
                  <th>平台状态</th>
                  <th>操作</th>
                </tr>
              </thead>
              <tbody>
                <?php if(is_array($list)): $i = 0; $__LIST__ = $list;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$item): $mod = ($i % 2 );++$i;?><tr class="gradeX">
                    <td><?php echo ($item["city_id"]); ?></td>
                    <td><?php echo ($item["city_name"]); ?></td>
                    <td><?php echo ($item["appid"]); ?></td>
                    <td><?php echo ($item["mchid"]); ?></td>
                    <td><?php echo ($item["platform"]); ?></td>
                    <td><?php echo (date('Y-m-d',$item["create_time"])); ?></td>
                    <td><?php if($item["status"] == 1): ?><font color="green">已启用</font> <?php else: ?><font color="red">未启用</font><?php endif; ?></td>
                    <td class="center">
                      <a href="<?php echo U('/city/edit', array('id'=>$item['id']));?>"><i class="fa fa-check text-navy"></i> 编辑</a> |
                      <a href="<?php echo U('/city/menuedit', array('id'=>$item['city_id']));?>"><i class="fa fa-check text-navy"></i> 编辑菜单</a>
                      <a href="javascript:void(0)" class="refresh-accesstoken" data-id="<?php echo ($item['city_id']); ?>"><i class="fa fa-check text-navy"></i> 刷新Token</a>
                    </td>
                  </tr><?php endforeach; endif; else: echo "" ;endif; ?>
              </tbody>
            </table>
            <div class="row ">
              <div class="pages">
              <?php echo ($page); ?>
                </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

</div>
<!-- 全局js -->
<script src="/Public/js/jquery.min.js?v=2.1.4"></script>
<script src="/Public/js/bootstrap.min.js?v=3.3.6"></script>
<script src="/Public/js/plugins/layer/layer.min.js"></script>
<!-- 自定义js -->
<script src="/Public/js/plugins/layer/laydate/laydate.js"></script>
<script src="/Public/js/content.js?v=1.0.0"></script>
<script src="/Public/js/active-msdt.js?v=1.0.0"></script>

<!-- Page-Level Scripts -->
<script type="text/javascript">
    $(document).ready(function () {
        $(".refresh-accesstoken").click(function () {
          var id = $(this).attr('data-id');
          $.ajax({
            type: "post",
            dataType: "json",
            data: {"id": id},
            url: "<?php echo U('/City/accesstoken');?>",
            success: function(data) {

              if (data.state == 200) {
                parent.layer.msg("刷新成功~");
              }else{
                parent.layer.msg(data.msg);
              }
            },
            error: function() {
              alert('删除失败，稍后再试');
            }
          });
        });
    });
</script>

<script type="text/javascript">
    $('.view_img').hover(function(e){
        var x = e.pageX;
        var y = e.pageY;
        if($(this).attr('data-url')){
            $(document.body).append("<div class='view_image_block'><img src='"+$(this).attr('data-url')+"' /> </div>");
            $('.view_image_block').css('left',x+'px');
            $('.view_image_block').css('top',y+'px');
        }

    },function(){
        $(".view_image_block").remove();
    });
</script>
</body>
</html>