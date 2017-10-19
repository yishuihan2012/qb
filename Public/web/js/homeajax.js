/**
 * Created by Administrator on 2016/9/2.
 */
/**
 * 获取验证码
 * @type {number}
 */


function sendMobileCode(obj) {
    var mobileValue=$("#mobileValue").val();
    if(mobileValue ==''){
        alert('请输入手机号');
        return false;
    }else{

        settime(obj);
        sendMobile(mobileValue);
    }

}
var countdown=60;
function settime(obj) {
    if(mobileValue==""){
        alert('输入手机号');
        return false;
    }
    if (countdown == 0) {
        obj.removeAttribute("disabled");
        obj.value="免费获取验证码";
        countdown = 60;
        return;
    } else {
        obj.setAttribute("disabled", true);
        obj.value="" + countdown + "秒后重新获取";
        countdown--;
    }
    setTimeout(function() {
            settime(obj) }
        ,1000)
}
function sendMobile(phoneNum) {
    $.ajax({
        type: 'POST',
        url: sendMobileCodeUrl,
        data:{'mobile':phoneNum},
        dataType:'json',
        success: function (data) {
            alert(data);
        },
    });
}
