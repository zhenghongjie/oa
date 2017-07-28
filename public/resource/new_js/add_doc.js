
$(document).ready(function(){
    //弹出框头部样式改变
    $(".select-box-nav li a ").each(function(){
        $(this).click(function(index){
            $(this).parent().siblings().find("a").removeClass("active")
            $(this).css("background-color","#258bd1");
            $(this).parent().siblings().find("a").css("background-color","#3497db");
        })
    });

    $(".select-box-nav li").each(function(index){
        $(this).click(function(event){
            $(".select-user-content").addClass("select-hidden")
            $(".select-user-content").eq(index).removeClass("select-hidden")
            $(".select-user-content").eq(index).addClass("select-show")
            event.stopPropagation();
        })
    });

    //弹出框部门点击样式
    $(".depart_item").click(function(event){
        var _that = $(this);
        $(".depart_item").css({"background-color":"#f9fbff","color":"#82939E"}); //其他的样式先还原
        $(_that).css({"background-color":"#3497DB","color":"white"});
        $("ul[data-pid="+$(_that).data('id')+"].depart_ul").css("display","block");
        $("ul[data-pid!="+$(_that).data('id')+"].depart_ul").css("display","none");
        event.stopPropagation();
    });

    $(".depart_item2").click(function(event){
        var _that = $(this);
        $(".depart_item2").css({"background-color":"#f9fbff","color":"#82939E"}); //其他的样式先还原
        $(_that).css({"background-color":"#3497DB","color":"white"});
        $("ul[data-pid="+$(_that).data('id')+"].depart_ul2").css("display","block");
        $("ul[data-pid!="+$(_that).data('id')+"].depart_ul2").css("display","none");
        event.stopPropagation();
    });
    //弹出框岗位点击样式
    $(".station_item").each(function(index){
        $(this).click(function(event){
            $(this).css("background-color","#3497DB")
            $(this).css("color","white")
            $(this).parent().parent().siblings().find("a").find(".station_item").css({"background-color":"#f9fbff","color":"#82939E"})
            event.stopPropagation();
            $(".station-aside-ul").eq(index).css("display","block")
            $(".station-aside-ul").eq(index).siblings().css("display","none")
        })
    });
    $(".station_item2").each(function(index){
        $(this).click(function(event){
            $(this).css("background-color","#3497DB")
            $(this).css("color","white")
            $(this).parent().parent().siblings().find("a").find(".station_item2").css({"background-color":"#f9fbff","color":"#82939E"})
            event.stopPropagation();
            $(".station-aside-ul2").eq(index).css("display","block")
            $(".station-aside-ul2").eq(index).siblings().css("display","none")
        })
    });
  //弹出框角色点击样式
    $(".role_item").each(function(index){
        $(this).click(function(event){
            $(this).css("background-color","#3497DB")
            $(this).css("color","white")
            $(this).parent().parent().siblings().find("a").find(".role_item").css({"background-color":"#f9fbff","color":"#82939E"})
            event.stopPropagation();
            $(".role-aside-ul").eq(index).css("display","block")
            $(".role-aside-ul").eq(index).siblings().css("display","none")
        })
    });
    $(".role_item2").each(function(index){
        $(this).click(function(event){
            $(this).css("background-color","#3497DB")
            $(this).css("color","white")
            $(this).parent().parent().siblings().find("a").find(".role_item2").css({"background-color":"#f9fbff","color":"#82939E"})
            event.stopPropagation();
            $(".role-aside-ul2").eq(index).css("display","block")
            $(".role-aside-ul2").eq(index).siblings().css("display","none")
        })
    });
    $("body").bind("click",function(){
        console.log('body click');
        $(".select2-selection--multiple").css("border-color","#dadfe6");
        $(".input-phone").css("border-color","#dadfe6");
    });
});







