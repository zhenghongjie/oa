$(document).ready(function(){
    function Sel(out_box_id) {
        this.$out_box = $('#' + out_box_id);
        this.$tag_box = this.$out_box.children('.tag-box').eq(0);
        this.$tag_del = this.$tag_box.find('.tag-del');
        this.$item_box = this.$out_box.children('.item-box').eq(0);
        this.$item = this.$item_box.children('.item');
        this.$sel_box = this.$out_box.children('.select-user-box').eq(0);
        this.$item2 = this.$sel_box.find('.item2');
        this.$click_all = this.$sel_box.find('.click-all');
        this.$btn_box = this.$out_box.children('.btn-box').eq(0);
        this.$sel_btn = this.$out_box;
        this.$man_btn = this.$btn_box.children('.man-btn').eq(0);
        this.$clean_btn = this.$btn_box.children('.clean-btn').eq(0);
        this.tag_arr = [];
        var _this = this;
        this.selBtnClick(this.$sel_btn, _this);
        this.manBtnClick(this.$man_btn, _this);
        this.cleanBtnClick(this.$clean_btn, _this);
        this.selBoxClick(this.$sel_box);
        this.itemClick(this.$item, _this);
        this.item2Click(this.$item2, _this);
        this.allClick(this.$click_all, _this);
    }
    Sel.prototype.selBtnClick = function (elem, _this){
        elem.click(function (event){
            var $ib = _this.$item_box;
            if($ib.css('display') == 'none'){
                console.log('item show');
                _this.hideBox('sel_box');
                $ib.css('display', 'block');
            } else{
                console.log('item hide');
                $ib.css('display', 'none');
            }
            event.stopPropagation();
        });
    }
    Sel.prototype.manBtnClick = function (elem, _this){
        elem.click(function (event){
            _this.hideBox('item_box');
            var $sb = _this.$sel_box;
            if($sb.css('display') == 'block'){
                console.log('sel hide');
                $sb.css('display', 'none');
            } else{
                console.log('sel show');
                $sb.css('display', 'block');
            }
            event.stopPropagation();
        });
    }
    Sel.prototype.cleanBtnClick = function (elem, _this){
        elem.click(function (event){
            console.log('clean');
            _this.$item2.each(function (){
                if($(this).prop('checked') == true){
                    $(this).click();
                }
            });
            _this.$click_all.each(function (){
                if($(this).prop('checked') == true){
                    $(this).prop('checked', false);
                }
            });
            _this.change();
            event.stopPropagation();
        });
    }
    Sel.prototype.selBoxClick = function (elem){
        elem.click(function (event){
            event.stopPropagation();
        });
    }
    Sel.prototype.itemClick = function (elem, _this){
        elem.each(function (){
            $(this).click(function (event){
                var value = $(this).data('value');
                var $item2 = _this.$item2;
                $item2.each(function (){
                    if($(this).data('id') == value){
                        $(this).click();
                        return false;
                    }
                });
                event.stopPropagation();
            });
        });
    }
    Sel.prototype.item2Click = function (elem, _this){
        elem.each(function (){
            $(this).click(function (event){
                var value = $(this).data('id');
                var html = $(this).data('title');
                if($(this).prop('checked') == false){
                    var $item = _this.$item;
                    $item.each(function (){
                        if($(this).data('value') == value){
                            $(this).removeClass('item-selected');
                        }
                    });
                    var $item2 = _this.$item2;
                    $item2.each(function (){
                        if($(this).data('id') == value && $(this).prop('checked') == true){
                            $(this).prop('checked', false);
                        }
                    });
                    var $tag = _this.$tag_box.children();
                    $tag.each(function (){
                        if($(this).data('value') == value){
                            $(this).remove();
                        }
                    });
                    var ta = _this.tag_arr;
                    for(var i in ta){
                        if(ta[i] == value){
                            _this.tag_arr.splice(i, 1);
                        }
                    }
                } else{
                    var $item = _this.$item;
                    $item.each(function (){
                        if($(this).data('value') == value){
                            $(this).addClass('item-selected');
                        }
                    });
                    var $item2 = _this.$item2;
                    $item2.each(function (){
                        if($(this).data('id') == value && $(this).prop('checked') == false){
                            $(this).prop('checked', true);
                        }
                    });
                    var $tag = $('<li class="tag" data-value="' + value + '">' + html + '<span class="tag-del">X</span></li>');
                    _this.$tag_box.append($tag);
                    _this.tag_arr.push(value);
                    _this.$tag_del = _this.$tag_box.find('.tag-del');
                    _this.tagDelClick(_this.$tag_del, _this);
                }
                _this.change();
                event.stopPropagation();
            });
        });
    }
    Sel.prototype.allClick = function (elem, _this){
        elem.each(function (){
            $(this).click(function (){
                var pid = $(this).data('pid');
                if($(this).prop('checked') == true){
                    _this.$item2.each(function (){
                        if($(this).data('pid') == pid && $(this).prop('checked') == false){
                            $(this).click();
                        }
                    });
                } else{
                    _this.$item2.each(function (){
                        if($(this).data('pid') == pid && $(this).prop('checked') == true){
                            $(this).click();
                        }
                    });
                }
            });
        });
    }
    Sel.prototype.tagDelClick = function (elem, _this){
        elem.each(function (){
            console.log('tagDelClick');
            $(this).get(0).onclick = function (event){
                var $tag = $(this).closest('.tag');
                var value = $tag.data('value');
                _this.$item2.each(function (){
                    if($(this).data('id') == value){
                        $(this).click();
                        return false;
                    }
                });
//                _this.change();
                var e = window.event || event;
                if(e.stopPropagation){
                    e.stopPropagation();
                } else{
                    window.event.cancelBubble = true;
                }
            };
        });
    }
    Sel.prototype.hideBox = function (status){
        if(status == undefined){ status = 'item_box';}
        switch (status){
            case 'item_box':
                this.$item_box.css('display', 'none');
                break;
            case 'sel_box':
                this.$sel_box.css('display', 'none');
                break;
            case 'all':
                this.$item_box.css('display', 'none');
                this.$sel_box.css('display', 'none');
                break;
        }
    }
    Sel.prototype.getTagArr = function (){
        return this.tag_arr;
    }
    Sel.prototype.changeFunc = function (func){
        if(typeof func == 'function'){
            this.changeFunc = func;
        }
    }
    Sel.prototype.change = function (){
        this.changeFunc(this.getTagArr());
    }
    $(function (){
        var sel0 = new Sel('out_box_left');
        var sel1 = new Sel('out_box_right');
        $('body').click(function (event){
            sel0.hideBox('all');
            sel1.hideBox('all');
        });
        sel0.changeFunc(function (arr){
            var str = arr.join(',');
            console.log('sel0:', str);
            $('#doc_publish').attr('value', str);
        });
        sel1.changeFunc(function (arr){
            var str = arr.join(',');
            console.log('sel1:', str);
            $('#doc_cc').attr('value', str);
        });

        var $state_box = $('.state-box');
        $state_box.click(function (){
            $state_box.each(function (){
                $(this).removeClass('label-active');
            });
            $(this).addClass('label-active');
        });

        var upsize = 0;
        var uparr = [];
        var fileinput = document.getElementById('fileinput');
        var input_index = 0;
        fileinput.onchange = function (){
            console.log('yuansheng!');
//        $('#fileinput').change(function (){
            console.log('file change');
            var formData = new FormData();
            formData.append('file', $('#fileinput').get(0).files[0]);
            console.log('formData msg:', formData.get('file'));
            if(formData.get('file') == 'undefined'){
                console.log('no file');
                return;
            }
            upsize += parseInt(formData.get('file').size);
            if(formData.get('file').size > 52400000){
                alert('文件大小超过50M');
                return;
            }
            if(upsize > 52400000){
                alert('文件总大小超过50M');
                return;
            }
            $.ajax({
                url:"{:url('base/base/upload_files','','',true)}",
                type: 'POST',
                data: formData,
                async: false,
                cache: false,
                contentType: false,
                processData: false,
                success: function (obj){
                    console.log('submit succ', obj);
                    if(obj.state == 1){
                        var annex_file_name = $('<input type="hidden" class="ig' + input_index + '" data-name="obj.data.file_name" data-id="'+annex_index+'"  name="doc_annex['+annex_index+'][file_name]" value="'+obj.data.file_name+'"/>');
                        var annex_file_size = $('<input type="hidden" class="ig' + input_index + '" data-id="'+annex_index+'" name="doc_annex['+annex_index+'][file_size]" value="'+obj.data.file_size+'"/>');
                        var annex_file_url = $('<input type="hidden" class="ig' + input_index + '" data-id="'+annex_index+'"  name="doc_annex['+annex_index+'][file_url]" value="'+obj.data.file+'"/>');
                        var annex_file_type = $('<input type="hidden" class="ig' + input_index + '" data-id="'+annex_index+'" name="doc_annex['+annex_index+'][file_type]" value="'+obj.data.file_type+'"/>');
                        $("#doc_submit_form").append(annex_file_name);
                        $("#doc_submit_form").append(annex_file_size);
                        $("#doc_submit_form").append(annex_file_url);
                        $("#doc_submit_form").append(annex_file_type);
                        upfileShow(obj.data);

                        input_index++;
                        annex_index++;
                    } else{
                        $('#file_msg').html('上传失败');
                    }
                },
                error: function (data) {
                    console.log('submit fail', data);
                    $('#file_msg').html('上传失败');
                }
            });
        };
        function upfileShow(obj){
            var tempobj = {
                'file_name': obj.file_name,
                'file_url': obj.file,
                'size': obj.file_size
            };
            uparr.push(tempobj);
            console.log('uparr:', uparr);
            $('#doc_annex').attr('value', uparr);
            var $file_msg = $('#file_msg');
            var $filetag = $('<div class="file">' + obj.file_name
                + '<span class="file-del" data-filename="'
                + obj.file_name + '">删除</span></div>');
            $filetag = fileDelBind($filetag, obj);
            $file_msg.append($filetag);
        }
        function fileDelBind(filetag, obj){
            var $filetag = filetag;
            var $temp = $filetag.children('.file-del');
            $temp.click(function (index){
                var filename = obj.file_name;
                var filesize = obj.file_size;
                for(var i in uparr){
                    if(uparr[i].file_name == filename){
                        uparr.splice(i, 1);
                        console.log('uparr del', uparr);
                    }
                }
                $(this).parent().remove();
                console.log('je:',$(this).parent());
                console.log('input_index:',index);
                $('.ig' + index).remove();
                console.log('filename:',filename);

                upsize -= filesize;
                $('#doc_annex').attr('value', uparr);
            }.bind($temp, input_index));
            return $filetag;
        }
    });
    var ue = UE.getEditor('doc_content',{
        serverUrl:'{:url("base/base/ue_upload")}',
        initialFrameWidth :740,
        initialFrameHeight:340,
        autoHeightEnabled:false

    });
});
