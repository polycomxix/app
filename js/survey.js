$(function(){
    
    var cookies_expire = new Date();
    cookies_expire.setTime(cookies_expire.getTime() + (30*60*1000));// expires after 30 minute

    //console.log(Cookies.get("agreement"));
    //if(Cookies.get("agreement"))
    //{
        //Cookies.remove('agreement');
    //}
    //else
    //{
        //window.location = "./";
    //}
    //Cookies.set("pid","2");
    /*----------Default Script--------------*/
    //Show other textbox after selected Other choice
    $("input[type=radio], input[type=checkbox]").on("click",function(){
        var $that = $(this);
        //console.log($that.val());
        if($that.val()=="other" && $that.is(":checked"))
        {
            $that.parents().eq(2).find(".txt-other").show().prop("disabled",false);
        }
        else{
            $that.parents().eq(2).find(".txt-other").hide().prop("disabled",true);
        }
    });

    

	//Check skipbox then disable radio
	$(".skip-box input[type=checkbox], .skip-box input[type=radio]").on("click", function(){
		if($(this).is(':checked'))
		{
			$.each($(this).closest(".question").find(".choices input"),function(){
				$(this).prop("disabled",true);
			});
            $(this).closest(".question").find(".form-group").removeClass("has-error").find(".help-block").empty();
			//$(this).parents().eq(3).find("input[type=radio]").prop("disabled",true);
			//$(this).parents().eq(3).find(".choices input[type=checkbox]").prop("disabled",true);
		}
		else
		{
			$.each($(this).closest(".question").find(".choices input"),function(){
				$(this).prop("disabled",false);
			});
		}
	});

	/*-----Checkbox to show rating scale--------*/
	$(".section-b .p2-rating").click(function(){
		
		if($(this).is(':checked'))
		{
			var $likert = $(this).parents().eq(3).find(".likert");
            $likert.show();
            $.each($likert.find("input[type=radio]"),function(){
                if($(this).is(":checked"))
                    $(this).parents().eq(3).find(".activity-detail").show();
            });
            $likert.find("input[type=radio]").click(function(){
                $(this).parents().eq(3).find(".activity-detail").show();
            });
            //$(this).parents().eq(3).find(".activity-detail").show();
        }
        else
        {
           $(this).parents().eq(3).find(".likert").hide();
           $(this).parents().eq(3).find(".activity-detail").hide();
       }
   });
 /*-----END Checkbox to show rating scale--------*/


	//Step wizard
	var navListItems = $('ul.setup-panel li a'),
    allWells = $('.setup-content');

    allWells.hide();

    navListItems.click(function(e)
    {
        e.preventDefault();
        var $target = $($(this).attr('href')),
        $item = $(this).closest('li');
        
        if (!$item.hasClass('disabled')) {
            navListItems.closest('li').removeClass('active');
            $item.addClass('active');
            allWells.hide();
            $target.show();
        }
    });
    
    $('ul.setup-panel li.active a').trigger('click');
    
    // DEMO ONLY //
    //Previous to step 1
    $('#previous-step-1').on('click', function(e) {
    	$('ul.setup-panel li:eq(1)').removeClass('active');
    	$('ul.setup-panel li:eq(1)').addClass('disabled');
        $('ul.setup-panel li:eq(0)').removeClass('disabled');
        $('ul.setup-panel li:eq(0)').addClass('active');
        $('ul.setup-panel li a[href="#section-a"]').trigger('click');
        $(this).remove();
    });
    //Next to step 2
    /*$('#next-step-2').on('click', function(e) {
        next_step_2();
    });*/
    function next_step_2(){
        //$('ul.setup-panel li:eq(0)').removeClass('active');
        $('ul.setup-panel li:eq(0)').addClass('disabled');
        $('ul.setup-panel li:eq(1)').removeClass('disabled');
        //$('ul.setup-panel li:eq(1)').addClass('active');
        $('ul.setup-panel li a[href="#section-b"]').trigger('click');
        $('#next-step-2').remove();
        $("html body").animate({scrollTop:0},400);
    }
    function next_step_3(){
        //$('ul.setup-panel li:eq(1)').removeClass('active');
        $('ul.setup-panel li:eq(1)').addClass('disabled');
        $('ul.setup-panel li:eq(2)').removeClass('disabled');
        //$('ul.setup-panel li:eq(2)').addClass('active');
        $('ul.setup-panel li a[href="#section-c"]').trigger('click');
        $(this).remove();
        $("html body").animate({scrollTop:0},400);
    }
    //Previous to step 2
    $('#previous-step-2').on('click', function(e) {
    	$('ul.setup-panel li:eq(2)').removeClass('active');
    	$('ul.setup-panel li:eq(2)').addClass('disabled');
        $('ul.setup-panel li:eq(1)').removeClass('disabled');
        $('ul.setup-panel li:eq(1)').addClass('active');
        $('ul.setup-panel li a[href="#section-b"]').trigger('click');
        $(this).remove();
    });

    //Next to step 3
    /*$('#next-step-3').on('click', function(e) {
    	//$('ul.setup-panel li:eq(1)').removeClass('active');
    	$('ul.setup-panel li:eq(1)').addClass('disabled');
        $('ul.setup-panel li:eq(2)').removeClass('disabled');
        //$('ul.setup-panel li:eq(2)').addClass('active');
        $('ul.setup-panel li a[href="#section-c"]').trigger('click');
        $(this).remove();
        $("html body").animate({scrollTop:0},400);
    });*/

    /*$(".btn-finish").click(function(){
        //submit form
        //go to thank you page and quizzes recommend
        window.location.href="./thanks.html";
    });*/

    /*----------End Default Scrip--------------*/


    /*----------Validate Form--------------*/
    //Validate form checking
    var sdata = {};
    
    $("#frm-a").validator().on("submit",function(e){
        $(".screen-loading").hide();
        if(e.isDefaultPrevented())
        {
            console.log("invalid");
        }
        else
        {
            e.preventDefault();
            //console.log("success1");
            var frm_a_data = $("#frm-a").serializeArray();
            $.each(frm_a_data, function(i,field){
                sdata[field.name] =field.value;
            });
            sdata["pid"]=Cookies.get("pid");
            next_step_2();
        }
    });

    var f_b_submit = false;//indicate if form is being submited
    $("#frm-b").on('submit', function (e) {        
        if(f_b_submit){//skip execution if form has been summitted and reset submit status
            console.log("continue submit");
            f_b_submit = false;
        }
        else{
            //load loading animation before submit again
            console.log("first submit");
            //set form submit status to prevent loop execution
            //after loading animation has been shown
            f_b_submit = true;
            e.preventDefault(); //prevent normal submit handler
            e.stopImmediatePropagation(); //stop other handler to be executed
            $(".screen-loading").show({
                duration: 0,
                complete: function(e){
                    console.log("complete");
                    setTimeout(function(){
                        console.log("resubmit");
                        $("#frm-b").submit();    
                    }, 0);
                    
                }
            });
        }
    }).validator({
        custom: {
                chkgrp: function ($el) {
                  var name = $el.data('chkgrp');
                  //var $checkboxes = $el.closest('form').find('input[name="' + name + '"]');
                  var $checkboxes = $el.closest('form').find('input[type=checkbox].'+name);

                  return $checkboxes.is(':checked');
                },
        },
        errors: {
                chkgrp: 'Need at least one checked',
        },
    }).on('change.bs.validator', '[data-chkgrp]', function (e) {
        var $el  = $(e.target)
        var name = $el.data('chkgrp')
        //console.log(name);
        //var $checkboxes = $el.closest('form').find('input[name="' + name + '"]')
        var $checkboxes = $el.closest('form').find('input[type=checkbox].'+name);
        $checkboxes.not(':checked').trigger('input');

    }).on("validate.bs.validator",function(e){
        $("#next-step-3").prop("disabled",false);
    }).on("submit",function(e){
        console.log("last submit");
        if(e.isDefaultPrevented())
        {
            console.log("invalid");
            var $target = $("#next-step-3").closest("form").find(".has-error");
            $(".screen-loading").hide();
            //$("html, body").animate({ scrollTop: $target.offset().top-100},400);
        }
        else
        {
            e.preventDefault();
            //console.log("success2");
            var frm_b_data = $("#frm-b").serializeArray();
            //console.log(frm_b_data);
            //console.log(frm_b_data["txt_b_q2"]);
            $.each(frm_b_data, function(i,field){
                //console.log(field.name+"|"+field.value);
                sdata[field.name] =field.value;
            });
            sdata["condition"] = 1; //1 create new user record | 2 insert addiction data
            if(Cookies.get("pid"))
            {
                sdata["pid"] = Cookies.get("pid");
            }
            console.log(sdata);
            $.ajax({
                type: "POST",
                url: "./php/survey.php",
                dataType: 'json',
                data: 
                { 
                    param: JSON.stringify(sdata)
                },
                success: function(data){
                    console.log(data.status);
                    $(".screen-loading").hide();
                    if(!Cookies.get("pid"))
                    {
                        Cookies.set("pid",data.pid,{expires:cookies_expire});
                    }
                   console.log(Cookies.get("pid"));
                    next_step_3();
                },
                error: function (jqXHR, exception) {
                    $(".screen-loading").hide();
                    console.log(jqXHR);
                    // Your error handling logic here..
                }
            });
        }
    });
    
    var f_c_submit = false;//indicate if form is being submited
    $("#frm-c").on('submit', function (e) {
        if(f_c_submit){//skip execution if form has been summitted and reset submit status
            console.log("continue submit");
            f_c_submit = false;
        }
        else{
            //load loading animation before submit again
            console.log("first submit");
            //set form submit status to prevent loop execution
            //after loading animation has been shown
            f_c_submit = true;
            e.preventDefault(); //prevent normal submit handler
            e.stopImmediatePropagation(); //stop other handler to be executed
           $(".screen-loading").show({
                duration: 0,
                complete: function(e){
                    console.log("complete");
                    setTimeout(function(){//send to function call queue
                        console.log("resubmit");
                        $("#frm-c").submit();    
                    }, 0);
                    
                }
            });
            /*0, function(){
                    console.log("complete");
                    //submit form again after loading animate show complete
                    $("#frm-c").submit();
            });*/
        }
    }).validator().on("submit",function(e){
        console.log(Cookies.get("pid"));
        if(e.isDefaultPrevented())
        {
            //$(".screen-loading").hide();
            console.log("invalid");
            $(".screen-loading").hide();
            var $target = $("#next-step-4").closest("form").find(".has-error");
            $("html, body").animate({ scrollTop: $target.offset().top-100},400);
        }
        else
        {
            e.preventDefault();
            console.log("success3");
            //submit data
            var frm_c_data = $("#frm-c").serializeArray();
            var sdata_c ={};
            $.each(frm_c_data, function(i,field){
                //console.log(field.name+"|"+field.value);
                sdata_c[field.name] =field.value;
            });
            sdata_c["condition"] = 2; //1 create new user record | 2 insert addiction data
            sdata_c["pid"] = Cookies.get("pid");
            console.log(sdata_c);
            $.ajax({
                type: "POST",
                url: "./php/survey.php",
                dataType: 'json',
                data: 
                { 
                    param: JSON.stringify(sdata_c)
                },
                success: function(data){
                    console.log(data.status);
                    $(".screen-loading").hide();
                    setTimeout(function(){
                        window.location.href="./thanks.html?point="+data.total;
                    },1000);
                    
                },
                error: function (jqXHR, exception) {
                    $(".screen-loading").hide();
                    console.log(jqXHR);
                    // Your error handling logic here..
                }
            }); 
            
        }
    }).on("validate.bs.validator",function(e){
        $("#next-step-4").prop("disabled",false);
    });

    /*----------End Validate Form--------------*/

    /*$(".btn-start").click(function(){
        $(".agreement").hide();
        $(".surveycontent").show();
    });*/
    /*$(".btn-agreement-decline").click(function(){
        window.location.href="./";
    });*/
        $("#modal-agreement").on('shown.bs.modal',function(event){
            var button = $(event.relatedTarget);
            var recipient = button.data('btnid');
            console.log(recipient);
            $(".btn-agree").click(function(){
                Cookies.remove('agreement');
                Cookies.set("agreement","1");
                $('#modal-agreement').modal('hide');
                $(".agreement").hide();
                $(".surveycontent").show();
            });
        });
});