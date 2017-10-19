/**
 * Created by Administrator on 2016/8/17.
 */
//后台编辑器上传功能
function sendFile(file,editor,$editable) {
    data = new FormData();
    data.append("file", file);
    //console.log(data);
    $.ajax({
        data: data,
        type: "POST",
        url: PicUploadUrl,
        cache: false,
        contentType: false,
        processData: false,
        beforeSend:function(){
            $(".fakeloader").show();
        },
        complete:function(){
            $(".fakeloader").hide();
        },
        success: function(url) {
            var a=eval(url);
            //写入summernote文本编辑框

            editor.insertImage($editable,a[0]);
        }
    });
}

/*
function send_File(file,t) {
    $.ajax({
        data: data,
        type: "POST",
        url: "/index.php/MrPiAdmin/upload/summernote.html",
        cache: false,
        contentType: false,
        processData: false,
        beforeSend:function(){
            $(".fakeloader").show();
        },
        complete:function(){
            $(".fakeloader").hide();
        },
        success: function(url) {
            var a=eval(url);
            $("input[name='a_image']").val(a[0]);
        }
    });
}*/
