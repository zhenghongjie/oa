/**
 * Created by Administrator on 2017/6/10 0010.
 */
/**
 * Created by Administrator on 2017/6/8 0008.
 */
$(document).ready(function(){
    $("#article-list li a").each(function(){
        $(this).click(function(){
            $(this).css("background-color","#009CDA")
            $(this).parent().next().children("a").css("background-color","#f9f9f6;")
        })

    });
    //搜索框
    $(".article-search").focus(function(){
        console.log(1)
        $(".search-cover").css("display","block")
        $(".shezhi").css("display","none")
    });
    $(".article-search").blur(function(){
        $(".search-cover").css("display","none")
        $(".shezhi").css("display","block")

    });

//下拉框显示和隐藏

    $("input[type='checkbox']").click(function(event){
        if($("input[type='checkbox']").is(":checked")){
            $(".am-dropdown").css("display","block");
            $(".am-dropdown-content").css("display","block")
        }else{
            $(".am-dropdown").css("display","none");
            $(".am-dropdown-content").css("display","none")
        }
        event.stopPropagation();
    });

    $("body").click(function(){
        $(".am-dropdown-content").css("display","none");
        console.log(33)
    });

    $(".am-dropdown").click(function(event){
        $(".am-dropdown-content").toggle();
        event.stopPropagation();
    });
//全选和不全选
    $("#checkall").click(function(){
        if($(this).prop("checked")){
            $(this).prop("checked",true);
            $(":checkbox").prop("checked",true)
            $(".am-dropdown").css("display","block");
            $(".am-dropdown-content").css("display","block")
        }
        else{
            $(this).prop("checked",false)
            $(":checkbox").prop("checked",false)
            $(".am-dropdown").css("display","none");
            $(".am-dropdown-content").css("display","none")
        }
    });

    //table 右边两个icon
    $(".am-table tr").each(function(){
        $(this).hover(function(){
            $(this).find(".table-number").toggle();
            $(this).find(".table-edit").toggle();
        })
    });

    //遮罩层显示隐藏
    $(".table-edit-left").click(function(){
        $(".mask").css("display","block");
        $(".edit-mask").css("display","block");
        $(".del-mask").css("display","none");
    });
    $(".table-edit-right").click(function(){
        $(".mask").css("display","block");
        $(".edit-mask").css("display","none");
        $(".del-mask").css("display","block");

    });

    $(".mask-close").click(function(){
        $(".mask").css("display","none")
    });
    //高级设置弹出框
    $(".shezhi").click(function(){
        $(".set-dialog").css("display","block")
    });
    $(".dialog-close").click(function(){
        $(".set-dialog").css("display","none")
    });
    $(".dialog-button button").click(function(){
        $(".set-dialog").css("display","none")
    });

});
