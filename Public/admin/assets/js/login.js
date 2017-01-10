var Login = function() {
  var loginAjax = function(){
    var $btn = $("#submit").button('loading');
    var uname =$("#user").val();
    var passwd =$("#pass").val();
    $.ajax({
      url : "/b2wms.php?act=manage.dologin", 
      type: 'POST',
      data: {'username':uname,'password':passwd},
      success: function(e){
        var data = eval("("+e+")");
        if(data.failure){
          $('.alert-danger', $('.login-form')).show().find("span").text(data.msg);
          $("#submit").button("reset");
        }else{
          $('.alert-danger', $('.login-form')).alert("close");
          $('.alert-success', $('.login-form')).show().find("span").text("登录成功");
          location.href="/b2wms.php"
        }
      }
    })
     /*$.post("/b2wms.php?act=manage.dologin", $(".login-form").serialize(),function(form, action){
      alert(action)
     }).done(function(form, action){
      console.log(action);
      //location.href= "/b2wms.php"
     }).fail(function(form, action){
      alert(action)
     })*/
  }
  var handleLogin = function() {

    $('.login-form').validate({
      errorElement: 'span', //default input error message container
      errorClass: 'help-block', // default input error message class
      focusInvalid: false, // do not focus the last invalid input
      rules: {
        username: {
          required: true
        },
        password: {
          required: true
        }
      },

      messages: {
        username: {
          required: "Username is required."
        },
        password: {
          required: "Password is required."
        }
      },

      invalidHandler: function(event, validator) { //display error alert on form submit   
        $('.alert-danger', $('.login-form')).show().find("span").text("账号和密码不能为空");
      },

      highlight: function(element) { // hightlight error inputs
        $(element)
          .closest('.form-group').addClass('has-error'); // set error class to the control group
      },

      success: function(label) {
        label.closest('.form-group').removeClass('has-error');
        label.remove();
      },

      errorPlacement: function(error, element) {
        error.insertAfter(element.closest('.input-icon'));
      },

      submitHandler: function(form) {
        loginAjax()
      }
    });

    $('.login-form input').keypress(function(e) {
      if (e.which == 13) {
        if ($('.login-form').validate().form()) {
          loginAjax();
        }
        return false;
      }
    });
  }

  return {
    //main function to initiate the module
    init: function() {

      handleLogin();

    }

  };
}();