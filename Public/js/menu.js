$(function(){
	$('#j-menu-btn').click(function(){
		if(!$(this).hasClass('active')){
			$('#container').css('overflow','hidden').animate({'scrollTop':'0px'},0);
			$(this).addClass('active');
			$(".container-top").addClass('active');
			$('#j-navbar-menu').addClass('active');
			$('#container').addClass('active');
		}else{
			$('#container').css('overflow','auto');
			$(this).removeClass('active');
			$(".container-top").removeClass('active');
			$('#j-navbar-menu').removeClass('active');
			$('#container').removeClass('active');
		}
	});
	$('.box_pop_header a').click(function(){
		$(this).closest('.box_pop').removeClass('show');
	});
	$('#j-menu-back').click(function(){
		history.go(-1);
	});
	$('.navbar-menu-search button').click(function(){
		var text = $("#searchtxt").val();
		location.href = _host+'Web/Search/index/text/'+encodeURI(text);
	});
})