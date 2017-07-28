/**
 * Created by Administrator on 2017/6/3 0003.
 */
$(document).ready(function(){
    //点击显示或隐藏部件
    $(".meet-input").click(function(event){
        $(".meet-input").css({"height":"60px","padding-top":"6px"});
        event.stopPropagation()
    });
    $("body").click(function(){
        $(".meet-input").css("height","30px");
    });

//首页tab标签
    $(".tabfirst li").each(function(index){
        $(this).click(function(){
            $(this).siblings().removeClass("box_email-active")
            $(this).addClass("box_email-active")
            $(this).find("a").css("color","#3497DB");
            $(this).siblings().find("a").css("color","#82939E");
            $("div .box_email-content").removeClass("box_email-content-show")
            $("div .box_email-content").addClass("box_email-content-hide")
            $(".box_email-content").eq(index).removeClass("box_email-content-hide")
            $(".box_email-content").eq(index).addClass("box_email-content-show")
        })
    });
//回到顶部
    $('#top').on('click',function () {
        $('body').animate({scrollTop:'0'},500);
    });
//点击显示或隐藏部件
    $(".close-icon").click(function(){
        $(this).parent().parent().css("display","none");
        var $box_length=$(".ibox:visible").length;
        if($box_length%2==0){
            $(".add-panl").css("display","block");
            $(".add-ibox").css("display","none")
        }else{
            $(".add-panl").css("display","none");
            $(".add-ibox").css("display","block")
        };
    });
//遮罩层显示和隐藏
    $(".show_icon").click(function(){
        $(".mask").css("display","block");
        //input对应不显示模块就不选中
        var cl=$(".ibox:hidden").data("target");
        $("input[data-target="+cl+"]").prop("checked",false);
        $("input[data-target="+cl+"]").prop("checked",false).parent().find("span").css("color","#82939E")
    });
    $(".mask-close").click(function(){
        $(".mask").css("display","none");
    })
//恢复默认设置
    $(".panel-bottom").click(function(){
        $(".ibox").css("display","block");
        $(".mask").css("display","none");
        $(".add-panl").css("display","none");
        $(".add-ibox").css("display","none")
    });
//遮罩层input功能
    $(".show-box-input").click(function(){
        var val=$(this).data("target");
        if($(this).is(':checked')){
            $("."+val).css("display","block")
            $(this).parent().find("span").css("color","#3497DB")
        }else{
            $("."+val).css("display","none")
            $(this).parent().find("span").css("color","#82939E")
        };
        var $box_length=$(".ibox:visible").length;
        if($box_length%2==0){
            $(".add-panl").css("display","block");
            $(".add-ibox").css("display","none")
        }else{
            $(".add-panl").css("display","none");
            $(".add-ibox").css("display","block")
        };
        if($box_length==7){
            $(".add-panl").css("display","none");
            $(".add-ibox").css("display","none")
        }
    })
});
